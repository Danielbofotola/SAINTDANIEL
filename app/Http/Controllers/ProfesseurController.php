<?php

namespace App\Http\Controllers;

use App\Models\Professeurs;
use App\Models\AnneeScolaire;
use App\Models\DocumentProfesseur;
use App\Models\EmploiDuTemps;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfesseurController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $statut = (string) $request->query('statut', '');

        $professeurs = Professeurs::query()
            ->withCount(['affectationsCours as affectations_count' => fn ($query) => $query
                ->anneeEnCours()
                ->where('statut', 'actif')])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('postnom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('specialite', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when(in_array($statut, ['actif', 'inactif'], true), fn ($query) => $query->where('statut', $statut))
            ->orderBy('nom')
            ->orderBy('postnom')
            ->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.professeurs.index', [
            'professeurs' => $professeurs,
            'search' => $search,
            'statut' => $statut,
            'stats' => [
                'total' => Professeurs::count(),
                'actifs' => Professeurs::where('statut', 'actif')->count(),
                'inactifs' => Professeurs::where('statut', 'inactif')->count(),
            ],
        ]);
    }

    public function show(Professeurs $professeur): View
    {
        $professeur->load([
            'user',
            'documents' => fn ($query) => $query->where('statut', 'actif')->latest('created_at'),
            'affectationsCours' => fn ($query) => $query->anneeEnCours()->where('statut', 'actif')->with(['cours', 'classe']),
        ]);

        return view('admin.professeurs.show', compact('professeur'));
    }

    /** Carte professionnelle : badge + QR unique (carte de paiement / contrôle). */
    public function carte(Professeurs $professeur): View
    {
        return view('admin.professeurs.carte', [
            'professeur' => $professeur,
            'carte' => \App\Support\CarteIdentite::professeur($professeur),
        ]);
    }

    public function toggleStatus(Professeurs $professeur): RedirectResponse
    {
        $professeur->update(['statut' => $professeur->statut === 'actif' ? 'inactif' : 'actif']);

        return back()->with('success', 'Le statut du professeur a été mis à jour.');
    }

    public function emploiDuTemps(Professeurs $professeur): View
    {
        $entrees = EmploiDuTemps::query()
            ->with(['affectation.cours', 'affectation.classe', 'salle'])
            ->whereHas('affectation', fn ($query) => $query
                ->where('professeur_id', $professeur->id)
                ->anneeEnCours())
            ->orderBy('heure_debut')
            ->get();

        $creneaux = $entrees
            ->map(fn ($entree) => ['debut' => $entree->heure_debut, 'fin' => $entree->heure_fin])
            ->unique(fn ($creneau) => $creneau['debut'].'|'.$creneau['fin'])
            ->sortBy('debut')
            ->values();

        $grille = [];
        foreach ($entrees as $entree) {
            $grille[$entree->jour][$entree->heure_debut.'|'.$entree->heure_fin][] = $entree;
        }

        $totalMinutes = $entrees->sum(fn ($entree) => (strtotime($entree->heure_fin) - strtotime($entree->heure_debut)) / 60);

        return view('admin.professeurs.emploi-du-temps', [
            'professeur' => $professeur,
            'jours' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
            'creneaux' => $creneaux,
            'grille' => $grille,
            'totalHeures' => round($totalMinutes / 60, 1),
            'annee' => AnneeScolaire::enCours(),
        ]);
    }

    public function create(): View
    {
        return view('admin.professeurs.create');
    }

    public function edit(Professeurs $professeur): View
    {
        return view('admin.professeurs.edit', compact('professeur'));
    }

    public function update(Request $request, Professeurs $professeur): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'sexe' => ['required', 'in:M,F'],
            'specialite' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', Rule::unique('professeurs', 'email')->ignore($professeur->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'supprimer_photo' => ['nullable', 'boolean'],
            'statut' => ['required', 'in:actif,inactif'],
        ], [
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre professeur.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG.',
        ]);

        if ($request->hasFile('photo')) {
            if ($professeur->photo) {
                Storage::disk('public')->delete($professeur->photo);
            }
            $data['photo'] = $request->file('photo')->store('professeurs/photos', 'public');
        } elseif ($request->boolean('supprimer_photo') && $professeur->photo) {
            Storage::disk('public')->delete($professeur->photo);
            $data['photo'] = null;
        }

        unset($data['supprimer_photo']);
        $professeur->update($data);

        return redirect()->route('admin.professeurs.show', $professeur)->with('success', 'Le profil du professeur a été mis à jour.');
    }

    public function storeDocument(Request $request, Professeurs $professeur): RedirectResponse
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'type_document' => ['required', 'in:cv,diplome,contrat,piece_identite,autre'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [
            'titre.required' => 'Le titre du document est obligatoire.',
            'document.required' => 'Veuillez sélectionner un fichier.',
            'document.mimes' => 'Le fichier doit être au format PDF, JPG, JPEG ou PNG.',
            'document.max' => 'Le fichier ne doit pas dépasser 4 Mo.',
        ]);

        $fichier = $request->file('document');

        DocumentProfesseur::create([
            'professeur_id' => $professeur->id,
            'titre' => $data['titre'],
            'type_document' => $data['type_document'],
            'chemin_fichier' => $fichier->store('professeurs/documents', 'public'),
            'taille_ko' => (int) max(1, ceil($fichier->getSize() / 1024)),
            'statut' => 'actif',
        ]);

        return back()->with('success', 'Le document a été ajouté au dossier du professeur.');
    }

    public function destroyDocument(DocumentProfesseur $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->chemin_fichier);
        $document->delete();

        return back()->with('success', 'Le document a été supprimé du dossier.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'postnom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'sexe' => ['required', 'in:M,F'],
            'specialite' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100', 'unique:professeurs,email'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'statut' => ['required', 'in:actif,inactif'],
        ], [
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'photo.mimes' => 'La photo doit être au format JPG, JPEG ou PNG.',
        ]);

        $year = now()->format('Y');
        $number = Professeurs::where('matricule', 'like', 'PROF-'.$year.'-%')->count() + 1;
        do {
            $data['matricule'] = 'PROF-'.$year.'-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
            $number++;
        } while (Professeurs::where('matricule', $data['matricule'])->exists());

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('professeurs/photos', 'public');
        }

        Professeurs::create($data);

        return redirect()->route('admin.professeurs.create')->with('success', 'Le professeur a été créé avec succès.');
    }
}