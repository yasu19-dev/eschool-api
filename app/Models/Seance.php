<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasUuids; // Utilisation des UUIDs pour les identifiants uniques

    /**
     * Le nom de la table correspondant à la migration.
     */
    protected $table = 'seances';

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'formateur_id',
        'module_id',
        'groupe_id',
        'date',
        'creneau',
        'salle',
        'commentaire_prof'
        // Note : On ne met pas 'type' ici car il est calculé dynamiquement
    ];

    // --- ACCESSEURS (Logique métier automatique) ---

    /**
     * Détermine dynamiquement le type de séance (Présentiel / Distanciel).
     * Si la salle est vide ou contient "A DISTANCE", c'est du distanciel.
     */
    public function getTypeAttribute()
    {
        if (!$this->salle || strtoupper($this->salle) === 'A DISTANCE') {
            return 'distanciel';
        }
        return 'présentiel';
    }

    // --- RELATIONS ---

    /**
     * Relation vers le profil du formateur
     */
    public function formateur()
    {
        return $this->belongsTo(FormateurProfile::class, 'formateur_id');
    }

    /**
     * Relation vers le module enseigné
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Relation vers le groupe (classe)
     */
    public function groupe()
    {
        return $this->belongsTo(Groupe::class,'groupe_id');
    }
    // app/Models/Seance.php

    /**
     * Relation vers les absences marquées durant cette séance
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'seance_id');
    }

    /**
     * Relation vers les justificatifs liés à cette séance
     */
    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'seance_id');
    }

    // --- FONCTIONS DE CALCUL ---

    /**
     * Compte le nombre d'absents réels (exclut les retards)
     */
    public function countAbsentsReels()
    {
        return $this->absences()->where('est_en_retard', false)->count();
    }
}
