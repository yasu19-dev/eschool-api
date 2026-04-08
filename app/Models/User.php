<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; // 👈 AJOUTÉ

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids,SoftDeletes; // 👈 AJOUTÉ

     /**
     * Les rôles possibles pour un utilisateur.
     * Note : 'admin' est pour les directeurs, 'formateur' pour les formateurs, 'stagiaire' pour les stagiaires.
     */
    const ROLES = ['admin', 'formateur', 'stagiaire'];

    /**
     * Les attributs assignables en masse.
     * Note : J'ai retiré 'name' car il n'est pas dans ta migration 000000.
     */
    protected $fillable = [
        'email',
        'password',
        'role', // Nouveau champ direct
        'etat',
        'email_notifications',
        'push_notifications',
        'lastLogin',
    ];

    /**
     * Les attributs cachés pour la sérialisation.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
/**
     * --- LOGIQUE DE SUPPRESSION EN CASCADE ---
     * Cette méthode s'exécute automatiquement avant la suppression physique en base de données.
     */
    protected static function booted()
    {
        static::deleting(function ($user) {
            // 1. Nettoyage si c'est un Stagiaire
            if ($user->stagiaireProfile) {
                // On supprime d'abord les absences liées au profil pour éviter l'erreur de clé étrangère
                $user->stagiaireProfile->absences()->delete();
                // Puis on supprime le profil
                $user->stagiaireProfile->delete();
            }

            // 2. Nettoyage si c'est un Formateur
            if ($user->formateurProfile) {
                $user->formateurProfile->delete();
            }

            // 3. Nettoyage si c'est un Admin/Director
            if ($user->adminProfile) {
                $user->adminProfile->delete();
            }
        });
    }
    // --- RELATIONS AVEC LES PROFILS ---
    public function groupe()
{
    return $this->belongsTo(Groupe::class, 'group_id');
}

    public function stagiaireProfile()
    {
        return $this->hasOne(StagiaireProfile::class);
    }

    public function formateurProfile()
    {
        return $this->hasOne(FormateurProfile::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    // --- HELPERS DE RÔLES (Pratique pour tes Controllers) ---

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFormateur(): bool
    {
        return $this->role === 'formateur';
    }

    public function isStagiaire(): bool
    {
        return $this->role === 'stagiaire';
    }

    /**
     * Casts des attributs.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'lastLogin' => 'datetime',
        ];
    }
}
