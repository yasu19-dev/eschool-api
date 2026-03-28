<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiDuTempsPdf extends Model
{
    protected $guarded = [];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'groupe_id');
    }
}
