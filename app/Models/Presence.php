<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasUuids;
    protected $fillable = ['seance_id', 'stagiaire_id', 'est_absent', 'est_en_retard', 'est_justifie'];
    // protected $guarded = [];

    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }

}
