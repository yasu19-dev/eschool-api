<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DemandeAttestation extends Model
{
   use HasUuids;
    protected $guarded = [];

    public function stagiaire() { return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id'); }
}
