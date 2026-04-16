<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    protected $table = 'dossiers_medicaux';
    protected $guarded = []; // Autorise l'insertion de masse

    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }
}
