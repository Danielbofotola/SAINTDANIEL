<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Inscription;
use App\Models\Tuteur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EleveInscriptionController extends Controller
{
    public function create(): View
    {
        return view('admin.eleves.create', [
            'classes' => Classe::with('cycle')->where('statut', 'actif')->whereHas('cycle', fn ($query) => $query->where('statut', 'actif'))->orderBy('nom')->get(),
            'annees' => AnneeScolaire::where('statut', 'actif')->orderByDesc('is_encours')->orderByDesc('date_debut')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'classe_id' => ['required', Rule::exists('classes', 'id')->where(fn ($query) => $query
                ->where('statut', 'actif')
                ->whereIn('cycle_id', DB::table('cycles')->where('statut', 'actif')->select('id')))],
            'nom' => ['required', 'string', 'max:50'], 'postnom' => ['required', 'string', 'max:50'], 'prenom' => ['required', 'string', 'max:50'],
            'sexe' => ['required', 'in:M,F'], 'date_naissance' => ['required', 'date', 'before:today'], 'lieu_naissance' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'nom_mere' => ['required', 'string', 'max:100'], 'telephone_mere' => ['required', 'string', 'max:20'], 'profession_mere' => ['nullable', 'string', 'max:100'], 'email_mere' => ['nullable', 'email', 'max:100'], 'adresse_mere' => ['nullable', 'string'],
            'nom_pere' => ['nullable', 'string', 'max:100'], 'telephone_pere' => ['nullable', 'string', 'max:20'], 'profession_pere' => ['nullable', 'string', 'max:100'], 'email_pere' => ['nullable', 'email', 'max:100'], 'adresse_pere' => ['nullable', 'string'],
            'groupe_sanguin' => ['nullable', 'string', 'max:5'], 'allergies_pathologies' => ['nullable', 'string'], 'traitements_urgences' => ['nullable', 'string'], 'medecin_referent' => ['nullable', 'string', 'max:150'],
            'acte_naissance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'], 'bulletin' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'], 'certificat_medical' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'acte_naissance.max' => "L'acte de naissance ne doit pas dépasser 4 Mo.",
            'bulletin.max' => 'Le bulletin ne doit pas dépasser 4 Mo.',
            'certificat_medical.max' => 'Le certificat médical ne doit pas dépasser 4 Mo.',
            '*.mimes' => 'Le fichier doit être au format PDF, JPG, JPEG ou PNG.',
        ]);

        // L'année de l'inscription est imposée par le serveur : l'année en cours.
        $anneeEnCours = AnneeScolaire::enCours();

        if (! $anneeEnCours) {
            return back()->withInput()->withErrors([
                'annee_scolaire_id' => "Aucune année scolaire n'est en cours. Définissez d'abord une année en cours avant d'inscrire un élève.",
            ]);
        }

        $data['annee_scolaire_id'] = $anneeEnCours->id;

        $eleve = DB::transaction(function () use ($request, $data) {
            $tuteur = Tuteur::create(collect($data)->only(['nom_mere','telephone_mere','profession_mere','email_mere','adresse_mere','nom_pere','telephone_pere','profession_pere','email_pere','adresse_pere'])->all());
            $photo = $request->file('photo')?->store('eleves/photos', 'public');
            $annee = AnneeScolaire::findOrFail($data['annee_scolaire_id']);
            $numero = str_pad((string) (Eleve::count() + 1), 4, '0', STR_PAD_LEFT);
            $eleve = Eleve::create(collect($data)->only(['nom','postnom','prenom','sexe','date_naissance','lieu_naissance'])->merge(['matricule' => 'ELV-'.Str::before($annee->libelle, '-').'-'.$numero, 'tuteur_id' => $tuteur->id, 'photo' => $photo, 'statut' => 'actif'])->all());
            DB::table('inscriptions')->insert(['eleve_id' => $eleve->id, 'classe_id' => $data['classe_id'], 'annee_scolaire_id' => $data['annee_scolaire_id'], 'date_inscription' => now()->toDateString(), 'statut' => Inscription::STATUT_EN_ATTENTE, 'created_at' => now(), 'updated_at' => now()]);
            if (collect($data)->only(['groupe_sanguin','allergies_pathologies','traitements_urgences','medecin_referent'])->filter()->isNotEmpty()) DB::table('eleve_sante')->insert(array_merge(['eleve_id' => $eleve->id, 'created_at' => now(), 'updated_at' => now()], collect($data)->only(['groupe_sanguin','allergies_pathologies','traitements_urgences','medecin_referent'])->all()));
            foreach (['acte_naissance', 'bulletin', 'certificat_medical'] as $type) if ($request->hasFile($type)) DB::table('documents_eleves')->insert(['eleve_id' => $eleve->id, 'type_document' => $type, 'chemin_fichier' => $request->file($type)->store('eleves/documents', 'public'), 'statut' => 'actif', 'created_at' => now()]);
            return $eleve;
        });

        return redirect()->route('admin.eleves.show', $eleve)->with('success', 'L’inscription de l’élève a été enregistrée. Elle est en attente de validation : la caisse l’activera automatiquement dès l’encaissement du frais d’inscription.');
    }
}
