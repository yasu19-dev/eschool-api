<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasUuids;

    /**
     * Nom de la table associée au modèle.
     * Indispensable car nous avons renommé 'presences' en 'absences'.
     */
    protected $table = 'absences';

    /**
     * Les attributs assignables en masse.
     * 'est_en_retard' : true = présent (retard), false = absent.
     */
    protected $fillable = [
        'seance_id',
        'stagiaire_id',
        'est_en_retard',
        'est_justifie',
        'motif'
    ];

    // --- RELATIONS ---

    /**
     * L'absence appartient à une séance.
     */
    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    /**
     * L'absence concerne un stagiaire spécifique.
     */
    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }


    /**
     * Casts pour transformer les colonnes tinyint en booléens propres.
     */
    protected $casts = [
        'est_en_retard' => 'boolean',
        'est_justifie' => 'boolean',
    ];
}
