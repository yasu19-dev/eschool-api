<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdminProfile extends Model
{
    use HasUuids;

    /**
     * Les attributs assignables en masse.
     * On y ajoute 'role_admin' pour la distinction Directeur/Responsable.
     */
    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'role_admin' // 'directeur' ou 'responsable_stagiaire'
    ];

    /**
     * Relation avec l'utilisateur principal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- HELPERS DE FONCTION ---

    /**
     * Vérifie si l'administrateur est le Directeur.
     */
    public function isDirecteur(): bool
    {
        return $this->role_admin === 'directeur';
    }

    /**
     * Vérifie si l'administrateur est le Responsable Stagiaire.
     */
    public function isResponsableStagiaire(): bool
    {
        return $this->role_admin === 'responsable_stagiaire';
    }
}
