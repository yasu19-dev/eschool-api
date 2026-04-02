<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasUuids;

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
        'type',
        'commentaire_prof'
    ];

    // --- RELATIONS ---

    public function formateur()
    {
        return $this->belongsTo(FormateurProfile::class, 'formateur_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    /**
     * Relation mise à jour : On pointe désormais vers le modèle Absence.
     */
    public function absences()
    {
        return $this->hasMany(Absence::class, 'seance_id');
    }
    public function countAbsentsReels()
    {
            // On compte ceux qui ne sont pas marqués "en retard"
            return $this->absences()->where('est_en_retard', false)->count();
        }

    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'seance_id');
    }
}
