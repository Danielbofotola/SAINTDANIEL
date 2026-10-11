<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Classe;
use App\Models\Cycle;
use App\Models\FraisScolaire;
use App\Models\Inscription;
use App\Models\MessageContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Partie publique de l'application (accessible sans compte) :
 * prÃ©sentation, informations d'inscription, Ã  propos et contact.
 *
 * Les vues correspondantes vivent dans resources/views/site et
 * utilisent le thÃ¨me vitrine de public/site (Start Bootstrap Â« Personal Â»).
 */
class PublicSiteController extends Controller
{
    public function accueil(): View
    {
        $anneeEnCours = AnneeScolaire::enCours();

        return view('site::accueil', [
            'anneeEnCours' => $anneeEnCours,
            'cycles' => $this->cyclesActifs(),
            'classesActives' => $this->classesActives(),
            'effectifInscrit' => $this->effectifInscrit($anneeEnCours),
        ]);
    }

    public function inscriptions(): View
    {
        $anneeEnCours = AnneeScolaire::enCours();

        return view('site::inscriptions', [
            'anneeEnCours' => $anneeEnCours,
            'cycles' => $this->cyclesActifs(),
            'classesActives' => $this->classesActives(),
            'fraisParClasse' => $this->fraisParClasse($anneeEnCours),
        ]);
    }

    public function apropos(): View
    {
        $anneeEnCours = AnneeScolaire::enCours();

        return view('site::apropos', [
            'anneeEnCours' => $anneeEnCours,
            'cycles' => $this->cyclesActifs(),
            'classesActives' => $this->classesActives(),
            'effectifInscrit' => $this->effectifInscrit($anneeEnCours),
        ]);
    }

    public function contact(): View
    {
        return view('site::contact', [
            'sujets' => MessageContact::SUJETS,
        ]);
    }

    public function storeContact(Request $request): RedirectResponse
    {
        // PiÃ¨ge anti-robot : un champ masquÃ© aux visiteurs. S'il est rempli,
        // on simule un succÃ¨s sans rien enregistrer.
        if ($request->filled('site_web')) {
            return $this->confirmationContact(null);
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'sujet' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'nom.required' => 'Indiquez votre nom et prÃ©nom.',
            'email.required' => 'Indiquez une adresse e-mail pour recevoir la rÃ©ponse.',
            'email.email' => 'Cette adresse e-mail nâ€™est pas valide.',
            'sujet.required' => 'Choisissez le motif de votre message.',
            'message.required' => 'Ã‰crivez votre message.',
            'message.min' => 'Votre message est trop court : dÃ©taillez votre demande (10 caractÃ¨res minimum).',
            'message.max' => 'Votre message est trop long : 3000 caractÃ¨res au maximum.',
        ]);

        $message = MessageContact::create([
            ...$data,
            'statut' => MessageContact::STATUT_NOUVEAU,
            'adresse_ip' => $request->ip(),
        ]);

        return $this->confirmationContact($message);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Cycle> */
    private function cyclesActifs()
    {
        return Cycle::query()
            ->where('statut', 'actif')
            ->with(['classes' => fn ($query) => $query->where('statut', 'actif')->orderBy('ordre')->orderBy('nom')])
            ->orderBy('nom')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Classe> */
    private function classesActives()
    {
        return Classe::query()
            ->where('statut', 'actif')
            ->whereHas('cycle', fn ($query) => $query->where('statut', 'actif'))
            ->with(['cycle', 'option'])
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();
    }

    /** Effectif des inscriptions actives de l'annÃ©e en cours (null si aucune annÃ©e ouverte). */
    private function effectifInscrit(?AnneeScolaire $anneeEnCours): ?int
    {
        if (! $anneeEnCours) {
            return null;
        }

        return Inscription::query()
            ->where('annee_scolaire_id', $anneeEnCours->id)
            ->where('statut', Inscription::STATUT_ACTIF)
            ->count();
    }

    /**
     * Frais scolaires réels de l'année en cours, par classe et par devise.
     *
     * @return \Illuminate\Support\Collection<int, array{classe: string, devise: string, total: float}>
     */
    private function fraisParClasse(?AnneeScolaire $anneeEnCours)
    {
        if (! $anneeEnCours || ! Schema::hasTable('frais_scolaires')) {
            return collect();
        }

        return FraisScolaire::query()
            ->where('annee_scolaire_id', $anneeEnCours->id)
            ->where('statut', 'actif')
            ->with('classe')
            ->get()
            ->groupBy(fn (FraisScolaire $frais) => $frais->classe_id.':'.($frais->devise ?? 'USD'))
            ->map(function ($lignes) {
                $premier = $lignes->first();

                return [
                    'classe' => $premier->classe?->nom ?? 'Classe',
                    'devise' => $premier->devise ?? 'USD',
                    'total' => round((float) $lignes->sum('montant_exige'), 2),
                ];
            })
            ->sortBy('classe')
            ->values();
    }

    private function confirmationContact(?MessageContact $message): RedirectResponse
    {
        $confirmation = 'Votre message a bien Ã©tÃ© transmis Ã  lâ€™administration.';

        if ($message) {
            $confirmation .= ' Votre rÃ©fÃ©rence : '.$message->reference().'.';
        }

        return redirect()
            ->route('site.contact')
            ->with('success', $confirmation.' Nous vous rÃ©pondons dans les meilleurs dÃ©lais aux coordonnÃ©es indiquÃ©es.');
    }
}
