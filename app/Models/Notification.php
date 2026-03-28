<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }
    
}
