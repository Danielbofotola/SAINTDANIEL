<?php

namespace App\Http\Controllers;

use App\Models\Paie;
use App\Models\Professeurs;
use App\Models\Salaire;
use App\Models\User;
use App\Support\Paie as ServicePaie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Gestion de la paie : définition du salaire de base des professeurs et des
 * autres membres du personnel, puis enregistrement des paiements de salaire
 * (un mois payé une seule fois par bénéficiaire). Accessible à l'admin et au
 * comptable (la vue est choisie selon le rôle).
 */
class PaieController extends Controller
{
    private const MODES = ['especes' => 'Espèces', 'banque' => 'Banque', 'mobile_money' => 'Mobile Money'];

    private const ROLES_PERSONNEL = ['comptable', 'titulaire', 'prefetture'];

    public function index(Request $request): View
    {
        $estAdmin = $request->user()->role === 'admin';

        $beneficiaires = collect()
            ->merge(Professeurs::where('statut', 'actif')->orderBy('nom')->get()->map(fn (Professeurs $professeur) => [
                'type' => 'professeur',
                'id' => $professeur->id,
                'nom' => trim($professeur->nom.' '.$professeur->postnom.' '.$professeur->prenom),
                'categorie' => 'Professeur'.($professeur->specialite ? ' — '.$professeur->specialite : ''),
            ]))
            ->merge(User::whereIn('role', self::ROLES_PERSONNEL)->where('statut', 'actif')->orderBy('nom')->get()->map(fn (User $utilisateur) => [
                'type' => 'utilisateur',
                'id' => $utilisateur->id,
                'nom' => trim($utilisateur->nom.' '.$utilisateur->postnom.' '.$utilisateur->prenom) ?: $utilisateur->username,
                'categorie' => 'Personnel — '.ucfirst($utilisateur->role),
            ]))
            ->values();

        $salaires = Salaire::with('beneficiaire')->get()
            ->keyBy(fn (Salaire $salaire) => $salaire->beneficiaire_type.':'.$salaire->beneficiaire_id);

        $paies = Paie::with('salaire.beneficiaire')
            ->latest('date_paiement')
            ->latest('id')
            ->limit(50)
            ->get();

        $moisCourant = now()->format('Y-m');

        return view($estAdmin ? 'admin.paies.index' : 'comptable.paies', [
            'user' => $request->user(),
            'beneficiaires' => $beneficiaires,
            'salaires' => $salaires,
            'paies' => $paies,
            'modes' => self::MODES,
            'moisCourant' => $moisCourant,
            'totalMois' => (float) Paie::where('statut', 'actif')->where('periode', $moisCourant)->sum('montant_net'),
            'totalAnnee' => (float) Paie::where('statut', 'actif')->where('periode', 'like', now()->format('Y').'-%')->sum('montant_net'),
            'masseSalariale' => (float) Salaire::where('statut', 'actif')->sum('salaire_base'),
            'estAdmin' => $estAdmin,
        ]);
    }

    public function storeSalaire(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'beneficiaire_type' => ['required', Rule::in(['professeur', 'utilisateur'])],
            'beneficiaire_id' => ['required', 'integer', 'min:1'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
        ], [
            'salaire_base.required' => 'Le salaire de base est obligatoire.',
            'beneficiaire_type.required' => 'Type de bénéficiaire invalide.',
        ]);

        $existe = $data['beneficiaire_type'] === 'professeur'
            ? Professeurs::whereKey($data['beneficiaire_id'])->exists()
            : User::whereKey($data['beneficiaire_id'])->exists();

        if (! $existe) {
            return back()->withInput()->with('error', 'Bénéficiaire introuvable.');
        }

        Salaire::updateOrCreate(
            ['beneficiaire_type' => $data['beneficiaire_type'], 'beneficiaire_id' => $data['beneficiaire_id']],
            ['salaire_base' => $data['salaire_base'], 'statut' => 'actif']
        );

        return back()->with('success', 'Le salaire de base a été enregistré.');
    }

    public function storePaie(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'salaire_id' => ['required', Rule::exists('salaires', 'id')->where('statut', 'actif')],
            'periode' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'primes' => ['nullable', 'numeric', 'min:0'],
            'retenues' => ['nullable', 'numeric', 'min:0'],
            'date_paiement' => ['required', 'date'],
            'mode_paiement' => ['required', Rule::in(array_keys(self::MODES))],
            'reference' => ['nullable', 'string', 'max:100'],
            'observation' => ['nullable', 'string', 'max:255'],
        ], [
            'salaire_id.required' => 'Choisissez le bénéficiaire.',
            'periode.required' => 'Choisissez le mois à payer.',
            'periode.regex' => 'Le mois doit être au format AAAA-MM.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
        ]);

        $salaire = Salaire::findOrFail($data['salaire_id']);

        try {
            $paie = ServicePaie::enregistrer($salaire, $data);
        } catch (RuntimeException) {
            return back()->withInput()->with('error', 'Ce mois est déjà payé pour '.$salaire->nomBeneficiaire().'.');
        }

        return back()->with('success', 'Salaire payé — '.$paie->num_paie.' pour '.$salaire->nomBeneficiaire()
            .' (net '.number_format((float) $paie->montant_net, 2, ',', ' ').').');
    }

    public function destroyPaie(Paie $paie): RedirectResponse
    {
        $paie->delete();

        return back()->with('success', 'Le paiement de salaire a été supprimé.');
    }
}
