<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Utilisateurs actuellement en ligne, déduits de la table `sessions`
 * (pilote de session « database ») : une session est considérée active si sa
 * dernière activité date de moins de FENETRE_MINUTES minutes.
 */
class PresenceUtilisateurs
{
    public const FENETRE_MINUTES = 5;

    /**
     * Sessions actives, éventuellement rattachées à leur compte utilisateur.
     */
    public static function enLigne(): Collection
    {
        if (! Schema::hasTable('sessions')) {
            return collect();
        }

        $depuis = now()->subMinutes(self::FENETRE_MINUTES)->getTimestamp();

        $sessions = DB::table('sessions')
            ->where('last_activity', '>=', $depuis)
            ->orderByDesc('last_activity')
            ->get();

        if ($sessions->isEmpty()) {
            return collect();
        }

        $utilisateurs = User::query()
            ->with('professeur')
            ->whereIn('id', $sessions->pluck('user_id')->filter()->unique()->values())
            ->get()
            ->keyBy('id');

        return $sessions->map(function (object $session) use ($utilisateurs) {
            $activite = Carbon::createFromTimestamp((int) $session->last_activity);

            return (object) [
                'session_id' => $session->id,
                'user' => $session->user_id ? $utilisateurs->get($session->user_id) : null,
                'ip' => $session->ip_address ?: '—',
                'appareil' => self::appareil($session->user_agent),
                'activite' => $activite,
                'minutes' => (int) $activite->diffInMinutes(now()),
                'est_connecte' => (bool) $session->user_id,
            ];
        });
    }

    /**
     * Identifiants des comptes ayant au moins une session active (badges).
     *
     * @return array<int, int>
     */
    public static function idsEnLigne(): array
    {
        if (! Schema::hasTable('sessions')) {
            return [];
        }

        $depuis = now()->subMinutes(self::FENETRE_MINUTES)->getTimestamp();

        return DB::table('sessions')
            ->where('last_activity', '>=', $depuis)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Libellé lisible du navigateur et du système d'exploitation.
     */
    public static function appareil(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        if ($ua === '') {
            return 'Appareil inconnu';
        }

        $navigateur = 'Navigateur inconnu';
        foreach ([
            'Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox',
            'Safari' => 'Safari', 'MSIE' => 'Internet Explorer', 'curl' => 'Client API',
        ] as $jeton => $nom) {
            if (str_contains($ua, $jeton)) {
                $navigateur = $nom;
                break;
            }
        }

        $systeme = '';
        foreach ([
            'Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad',
            'Mac OS' => 'macOS', 'Linux' => 'Linux',
        ] as $jeton => $nom) {
            if (str_contains($ua, $jeton)) {
                $systeme = ' · '.$nom;
                break;
            }
        }

        return $navigateur.$systeme;
    }
}
