<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiDuTempsPdf extends Model
{
  protected $table = 'emplois_du_temps_pdf';

    protected $fillable = [
        'groupe_id',
        'titre',
        'fichier_url',
        'format'
    ];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'groupe_id');
    }
}
