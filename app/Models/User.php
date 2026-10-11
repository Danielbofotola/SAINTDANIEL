<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function professeur()
    {
        return $this->belongsTo(Professeurs::class, 'professeur_id');
    }

    /**
     * Enfants rattachés à ce compte (utile pour un compte parent).
     */
    public function eleves()
    {
        return $this->belongsToMany(Eleve::class, 'eleve_user')->withTimestamps();
    }

    /**
     * Rôles reconnus, dans l'ordre d'affichage : source unique pour les listes
     * de validation et les sélecteurs de rôle.
     *
     * @var list<string>
     */
    public const ROLES = ['admin', 'systeme', 'prefetture', 'directeur_etudes', 'secretaire', 'disciplinaire', 'titulaire', 'professeur', 'comptable', 'parent'];

    /** Libellé lisible d'un rôle. */
    public const ROLE_LABELS = [
        'admin' => 'Administrateur',
        'systeme' => 'Système',
        'prefetture' => 'Préfecture',
        'directeur_etudes' => 'Directeur des Études',
        'secretaire' => 'Secrétaire',
        'disciplinaire' => 'Disciplinaire / Conseiller Pédagogique',
        'titulaire' => 'Titulaire',
        'professeur' => 'Professeur',
        'comptable' => 'Comptable',
        'parent' => 'Parent',
    ];

    public static function libelleRole(?string $role): string
    {
        return self::ROLE_LABELS[$role] ?? ucfirst((string) $role);
    }

    /**
     * Nom de route du tableau de bord correspondant au rôle.
     *
     * Source unique : login, « retour au tableau de bord » et impersonation s'y réfèrent.
     * Le repli est `profile.show` — jamais `home`, qui redirige selon le rôle (boucle).
     */
    public static function dashboardRouteNameFor(?string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'systeme' => 'poste.show',
            'directeur_etudes' => 'poste.show',
            'secretaire' => 'poste.show',
            'disciplinaire' => 'poste.show',
            'prefetture' => 'prefecture.dashboard',
            'titulaire' => 'titulaire.dashboard',
            'professeur' => 'professeur.dashboard',
            'comptable' => 'comptable.dashboard',
            'parent' => 'parent.dashboard',
            default => 'profile.show',
        };
    }

    public function dashboardRouteName(): string
    {
        return static::dashboardRouteNameFor($this->role);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'professeur_id',
        'nom',
        'postnom',
        'prenom',
        'date_naissance',
        'username',
        'email',
        'telephone',
        'adresse',
        'photo',
        'password',
        'pin_code',
        'role',
        'statut',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin_code',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
            'date_naissance' => 'date',
        ];
    }
}
