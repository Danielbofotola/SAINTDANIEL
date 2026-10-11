<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\ParametreSysteme;
use App\Support\Messagerie;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace Parent : communication dédiée aux parents, ouverte seulement quand
 * l'administrateur active le module « Portail Parents ».
 *
 * Chaque rubrique possède SA propre page (actualités, notifications, messages,
 * enfants, profil) : une requête ne charge que les données de la rubrique
 * affichée, ce qui évite l'embouteillage d'une page unique tout-en-un.
 */
class PortailParentController extends Controller
{
    /** Communique les annonces de l'établissement. */
    public function index(Request $request): View
    {
        return view('parent.actualites', $this->donneesDeBase() + [
            'annonces' => Annonce::query()
                ->where(fn ($query) => $query->where('cible', 'tous')->orWhere('cible', 'parent'))
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    /** Liste les notifications du parent. */
    public function notifications(Request $request): View
    {
        $base = $this->donneesDeBase();

        return view('parent.notifications', $base + [
            'notifications' => $base['user']->notifications()->latest()->limit(50)->get(),
        ]);
    }

    /** Messagerie du parent (un seul interlocuteur : la préfecture). */
    public function messages(Request $request): View
    {
        $base = $this->donneesDeBase();
        $utilisateur = $base['user'];

        $conversations = Messagerie::conversationsDe($utilisateur);

        $active = $request->query('conversation')
            ? $conversations->firstWhere('id', (int) $request->query('conversation'))
            : $conversations->first();

        if ($active) {
            Messagerie::marquerLu($active, $utilisateur);
            $active->setAttribute('non_lus', 0);
        }

        return view('parent.messages', $base + [
            'conversations' => $conversations,
            'active' => $active,
            'messages' => $active ? $active->messages()->with('auteur')->get() : collect(),
            'destinataires' => Messagerie::correspondantsQuery($utilisateur)->get(),
            'nonLus' => (int) $conversations->sum('non_lus'),
        ]);
    }

    /** Enfants rattachés au compte : identité, notes et travaux de la classe. */
    public function enfants(Request $request): View
    {
        $base = $this->donneesDeBase();
        $utilisateur = $base['user'];

        // Enfants rattachés à ce compte parent (lien posé par l'admin).
        $enfants = $utilisateur->role === 'parent'
            ? $utilisateur->eleves()
                ->with(['inscriptions' => fn ($query) => $query->anneeEnCours()->with('classe')])
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get()
            : collect();

        // Enfant consulté : un parent ne peut ouvrir que ses propres enfants.
        $enfantActif = $request->query('enfant')
            ? $enfants->firstWhere('id', (int) $request->query('enfant'))
            : null;

        $notes = collect();
        $travaux = collect();

        if ($enfantActif) {
            $notes = Note::query()
                ->where('eleve_id', $enfantActif->id)
                ->where('statut', 'actif')
                ->whereHas('evaluation', fn ($query) => $query->whereHas('affectation', fn ($affectation) => $affectation->anneeEnCours()))
                ->with(['evaluation.affectation.cours', 'evaluation.affectation.professeur', 'evaluation.periode'])
                ->get()
                ->sortByDesc(fn (Note $note) => (string) $note->evaluation?->date_evaluation)
                ->values();

            $classeIds = $enfantActif->inscriptions->pluck('classe_id')->filter()->unique();

            // Travaux (interrogations, devoirs, examens) de la classe de l'enfant,
            // avec la note obtenue quand elle existe déjà.
            $travaux = Evaluation::query()
                ->whereHas('affectation', fn ($query) => $query->anneeEnCours()->whereIn('classe_id', $classeIds))
                ->with(['affectation.cours', 'affectation.professeur', 'periode'])
                ->orderByDesc('date_evaluation')
                ->get()
                ->each(function (Evaluation $evaluation) use ($notes) {
                    $note = $notes->first(fn (Note $item) => (int) $item->evaluation_id === (int) $evaluation->id);
                    $evaluation->setAttribute('ma_note', $note?->note_obtenue);
                });
        }

        return view('parent.enfants', $base + [
            'enfants' => $enfants,
            'enfantActif' => $enfantActif,
            'notes' => $notes,
            'travaux' => $travaux,
        ]);
    }

    /**
     * Détail d'une notification : message complet, auteur, date et action associée.
     * Ouvrir le détail vaut lecture (le compteur de non-lues diminue).
     */
    public function notification(Request $request, string $notification): View
    {
        $base = $this->donneesDeBase();

        // Un parent ne peut ouvrir que SES notifications.
        $modele = $base['user']->notifications()->whereKey($notification)->firstOrFail();
        $modele->markAsRead();

        return view('parent.notification', $base + ['notification' => $modele]);
    }

    /** Fiche du compte parent. */
    public function profil(Request $request): View
    {
        return view('parent.profil', $this->donneesDeBase());
    }

    /**
     * Données communes à toutes les pages : utilisateur connecté et paramètres.
     * Le portail n'existe que si l'administration l'a ouvert.
     *
     * @return array{user: \App\Models\User, parametres: \App\Models\ParametreSysteme}
     */
    private function donneesDeBase(): array
    {
        $parametres = ParametreSysteme::current();

        abort_unless($parametres->portail_parents_actif, 404);

        return ['user' => request()->user(), 'parametres' => $parametres];
    }
}
