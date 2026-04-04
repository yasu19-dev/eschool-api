<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DemandeAttestation extends Model
{
   use HasUuids;
   protected $fillable = [
        'stagiaire_id',
        'type',
        'status',
        'motif_refus',
        'date_livraison_prevue'
    ];

    public function stagiaire() { return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id'); }
}
