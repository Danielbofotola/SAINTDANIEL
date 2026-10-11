<?php

namespace App\Providers;

use App\Models\ParametreSysteme;
use App\Models\AnneeScolaire;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vues de la partie publique (vitrine) : elles vivent dans le dossier
        // « page publique pour les inscription et info » et sont appelées via
        // le namespace `site` (ex. view('site::accueil')).
        View::addNamespace('site', resource_path('views/page publique pour les inscription et info'));

        // Derrière un proxy HTTPS (LocalTunnel, ngrok…), forcer la génération
        // d'URL/actifs en HTTPS pour éviter le contenu mixte HTTP/HTTPS.
        // On se base sur le proxy (X-Forwarded-Proto) OU sur le fait que la
        // requête vise déjà l'hôte public déclaré dans APP_URL.
        $publicHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (config('app.env') !== 'local'
            || request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || ($publicHost !== null && $publicHost !== '' && request()->getHost() === $publicHost)
        ) {
            URL::forceScheme('https');
        }

        // Bénéficiaires d'un salaire : un professeur (entité) ou un autre
        // membre du personnel (compte utilisateur).
        Relation::morphMap([
            'professeur' => \App\Models\Professeurs::class,
            'utilisateur' => \App\Models\User::class,
        ]);

        View::composer('*', static function ($view): void {
            $settings = request()->attributes->get('system_settings');
            if (! $settings) {
                $settings = self::firstOrNull(fn () => ParametreSysteme::query()->first());
                $settings ??= ParametreSysteme::defaults();
                request()->attributes->set('system_settings', $settings);
            }

            $view->with('systemSettings', $settings);

            if (! request()->attributes->has('annee_en_cours')) {
                request()->attributes->set('annee_en_cours', self::firstOrNull(
                    fn () => AnneeScolaire::where('is_encours', true)->first()
                ));
            }

            $view->with('anneeEnCours', request()->attributes->get('annee_en_cours'));
        });
    }

    /**
     * Exécute une lecture destinée à l'affichage et renvoie null en cas d'échec.
     *
     * Ces valeurs ne sont que du contexte d'affichage : une table absente ou
     * indisponible ne doit jamais faire échouer le rendu d'une page.
     */
    private static function firstOrNull(callable $query): mixed
    {
        try {
            return $query();
        } catch (\Throwable) {
            return null;
        }
    }

    
}
