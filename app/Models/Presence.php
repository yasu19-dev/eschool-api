<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasUuids;
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
