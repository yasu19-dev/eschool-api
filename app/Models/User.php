<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use App\Models\AdminProfile;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;
    use SoftDeletes;
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
    protected $dates = ['deleted_at'];

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
