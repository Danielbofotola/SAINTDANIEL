<?php

namespace App\Http\Controllers;

use App\Models\DocumentEleve;
use App\Models\Eleve;
use App\Models\AnneeScolaire;
use App\Models\Deliberation;
use App\Support\SituationEleve;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EleveController extends Controller
{
    /**
     * Active ou désactive un élève : « inactif » le fait disparaître des
     * listes, des effectifs et des écrans de suivi (sa fiche reste
     * consultable par son URL directe).
     */
    public function toggleStatut(Request $request, Eleve $eleve): RedirectResponse
    {
        $eleve->update(['statut' => $eleve->statut === 'actif' ? 'inactif' : 'actif']);

        $nom = trim($eleve->nom.' '.$eleve->prenom);

        return back()->with('success', $eleve->statut === 'actif'
            ? $nom.' est activé : il est de nouveau visible dans l\'application.'
            : $nom.' est désactivé : il n\'apparaît plus dans les listes ni les effectifs.');
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $statut = (string) $request->query('statut', '');

        // Année observée : celle choisie, sinon l'année en cours. Elle sert à
        // préparer l'année suivante — on y voit les élèves qui y seront (promus
        // et redoublants issus de la délibération) à côté des nouveaux inscrits.
        $anneeId = $request->integer('annee') ?: AnneeScolaire::enCours()?->id;
        $annee = $anneeId ? AnneeScolaire::find($anneeId) : null;

        // Portée commune : élèves SCOLARISÉS (hors désactivés et hors archivés)
        // inscrits dans l'année observée (inscription active ou « en attente »).
        // Les désactivés ont leur page dédiée ; les diplômés/renvoyés, les archives.
        $inscritsAnnee = fn () => Eleve::query()->scolarises()->when($annee, fn ($query) => $query->whereHas(
            'inscriptions',
            fn ($inscription) => $inscription
                ->where('annee_scolaire_id', $annee->id)
                ->whereIn('statut', ['actif', 'en_attente'])
        ));

        $eleves = $inscritsAnnee()
            ->with(['inscriptions' => fn ($query) => $query
                ->when($annee, fn ($q) => $q->where('annee_scolaire_id', $annee->id), fn ($q) => $q->anneeEnCours())
                ->whereIn('statut', ['actif', 'en_attente'])
                ->with('classe')])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('postnom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%");
            }))
            ->when(in_array($statut, ['actif', 'suspendu'], true), fn ($query) => $query->where('statut', $statut))
            ->orderBy('nom')
            ->orderBy('postnom')
            ->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        // Provenance de chaque élève pour l'année observée : la décision de fin
        // d'année précédente (admis → passage, redoublant/ajourné → maintien) ou
        // « nouveau » s'il n'en a pas.
        $origines = $this->origines($annee, $eleves->pluck('id')->all());

        return view('admin.eleves.index', [
            'eleves' => $eleves,
            'search' => $search,
            'statut' => $statut,
            'annee' => $annee,
            'annees' => AnneeScolaire::query()->orderByDesc('date_debut')->get(),
            'origines' => $origines,
            'stats' => [
                'total' => $inscritsAnnee()->count(),
                'garcons' => $inscritsAnnee()->where('sexe', 'M')->count(),
                'filles' => $inscritsAnnee()->where('sexe', 'F')->count(),
                'inscrits' => $inscritsAnnee()->count(),
            ],
        ]);
    }

    /** Page dédiée aux élèves désactivés (retirés des listes actives). */
    public function desactives(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $eleves = Eleve::query()
            ->desactives()
            ->with(['tuteur', 'inscriptions' => fn ($query) => $query->with('classe.cycle')->latest('id')])
            ->when($search !== '', fn ($query) => $this->recherche($query, $search))
            ->orderBy('nom')->orderBy('postnom')->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.eleves.desactives', compact('eleves', 'search'));
    }

    /** Archives : élèves sortis de l'école (diplômés, renvoyés), conservés. */
    public function archives(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $eleves = Eleve::query()
            ->archives()
            ->with([
                'inscriptions' => fn ($query) => $query->with('classe.cycle')->latest('id'),
                'certifications' => fn ($query) => $query->with('cycle')->latest('date_obtention'),
            ])
            ->when($search !== '', fn ($query) => $this->recherche($query, $search))
            ->orderBy('nom')->orderBy('postnom')->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.eleves.archives', compact('eleves', 'search'));
    }

    /** Filtre de recherche commun (nom, postnom, prénom, matricule). */
    private function recherche($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('nom', 'like', "%{$search}%")
                ->orWhere('postnom', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%");
        });
    }

    /**
     * Décision de délibération de l'année précédente, par élève (clé = eleve_id).
     *
     * @param  array<int, int>  $eleveIds
     * @return array<int, string>
     */
    private function origines(?AnneeScolaire $annee, array $eleveIds): array
    {
        if ($annee === null || $eleveIds === [] || ! Schema::hasTable('deliberations')) {
            return [];
        }

        $precedente = AnneeScolaire::precedente($annee);

        if ($precedente === null) {
            return [];
        }

        return Deliberation::query()
            ->join('inscriptions', 'inscriptions.id', '=', 'deliberations.inscription_id')
            ->where('deliberations.annee_scolaire_id', $precedente->id)
            ->whereIn('inscriptions.eleve_id', $eleveIds)
            ->pluck('deliberations.decision', 'inscriptions.eleve_id')
            ->all();
    }

    /** Carte d'identité de l'élève : badge + QR unique, présentable à la caisse. */
    public function carte(Eleve $eleve): View
    {
        $annee = AnneeScolaire::enCours();

        $inscription = $eleve->inscriptions()
            ->with(['classe', 'anneeScolaire'])
            ->when($annee, fn ($query) => $query->where('annee_scolaire_id', $annee->id))
            ->where('statut', 'actif')
            ->latest('id')
            ->first()
            ?? $eleve->inscriptions()->with(['classe', 'anneeScolaire'])->latest('id')->first();

        return view('admin.eleves.carte', [
            'eleve' => $eleve,
            'inscription' => $inscription,
            'carte' => \App\Support\CarteIdentite::eleve($eleve, $inscription),
        ]);
    }

    public function edit(Eleve $eleve): View
    {
        $eleve->load(['tuteur', 'sante', 'documents' => fn ($query) => $query->latest('created_at')]);

        return view('admin.eleves.edit', compact('eleve'));
    }

    public function update(Request $request, Eleve $eleve): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'sexe' => ['required', 'in:M,F'],
            'date_naissance' => ['required', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'statut' => ['required', 'in:actif,inactif,suspendu,exclu'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'nom_mere' => ['required', 'string', 'max:100'],
            'telephone_mere' => ['required', 'string', 'max:20'],
            'profession_mere' => ['nullable', 'string', 'max:100'],
            'email_mere' => ['nullable', 'email', 'max:100'],
            'adresse_mere' => ['nullable', 'string'],
            'nom_pere' => ['nullable', 'string', 'max:100'],
            'telephone_pere' => ['nullable', 'string', 'max:20'],
            'profession_pere' => ['nullable', 'string', 'max:100'],
            'email_pere' => ['nullable', 'email', 'max:100'],
            'adresse_pere' => ['nullable', 'string'],
            'groupe_sanguin' => ['nullable', 'string', 'max:5'],
            'allergies_pathologies' => ['nullable', 'string'],
            'traitements_urgences' => ['nullable', 'string'],
            'medecin_referent' => ['nullable', 'string', 'max:150'],
        ], $this->messages());

        DB::transaction(function () use ($request, $eleve, $data) {
            $photo = $request->file('photo')?->store('eleves/photos', 'public');
            $eleve->update(collect($data)->only(['nom', 'postnom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance', 'statut'])->merge($photo ? ['photo' => $photo] : [])->all());
            $eleve->tuteur()->update(collect($data)->only(['nom_mere', 'telephone_mere', 'profession_mere', 'email_mere', 'adresse_mere', 'nom_pere', 'telephone_pere', 'profession_pere', 'email_pere', 'adresse_pere'])->all());
            $eleve->sante()->updateOrCreate([], collect($data)->only(['groupe_sanguin', 'allergies_pathologies', 'traitements_urgences', 'medecin_referent'])->all());
        });

        return redirect()->route('admin.eleves.show', $eleve)->with('success', 'Le profil de l’élève a été modifié.');
    }

    public function storeDocument(Request $request, Eleve $eleve): RedirectResponse
    {
        $data = $request->validate([
            'type_document' => ['required', 'in:acte_naissance,bulletin,certificat_medical,autre'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [
            'document.required' => 'Veuillez sélectionner un fichier.',
            'document.max' => 'Le fichier ne doit pas dépasser 4 Mo.',
            'document.mimes' => 'Le fichier doit être au format PDF, JPG, JPEG ou PNG.',
        ]);

        DocumentEleve::create([
            'eleve_id' => $eleve->id,
            'type_document' => $data['type_document'],
            'chemin_fichier' => $request->file('document')->store('eleves/documents', 'public'),
            'statut' => 'actif',
            'created_at' => now(),
        ]);

        return back()->with('success', 'Le document a été ajouté au dossier.');
    }

    public function show(Eleve $eleve): View
    {
        $eleve->load([
            'tuteur',
            'sante',
            'documents' => fn ($query) => $query->where('statut', 'actif')->latest('created_at'),
        ]);

        // Seule l'inscription de l'année en cours est présentée sur le dossier.
        $inscription = $eleve->inscriptions()
            ->anneeEnCours()
            ->whereIn('statut', ['actif', 'en_attente'])
            ->with(['classe.cycle', 'classe.section', 'classe.option'])
            ->latest('date_inscription')
            ->first();

        // Parcours complet (années passées comprises) + état financier : même
        // logique que le dossier du préfet des études (App\Support\SituationEleve).
        $parcours = SituationEleve::parcours($eleve);

        return view('admin.eleves.show', compact('eleve', 'inscription') + [
            'parcours' => $parcours,
            'detteTotale' => SituationEleve::detteTotale($parcours),
            'paiements' => $inscription ? SituationEleve::paiements($inscription) : collect(),
            'certifications' => Schema::hasTable('certifications')
                ? $eleve->certifications()->with('cycle')->orderByDesc('date_obtention')->get()
                : collect(),
        ]);
    }

    private function messages(): array
    {
        return [
            '*.required' => 'Ce champ est obligatoire.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            '*.email' => 'Veuillez saisir une adresse e-mail valide.',
        ];
    }
}
