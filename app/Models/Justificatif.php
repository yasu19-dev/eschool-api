<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    protected $guarded = [];

    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }
}
