<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    use HasUuids;
   protected $fillable = [
        'stagiaire_id',
        'seance_id',
        'fichier_url',
        'statut',
        'est_valide',
        'type'
    ];

    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

   public function stagiaireProfile()
    {
        // On lie stagiaire_id de cette table à l'id de StagiaireProfile
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }
}
