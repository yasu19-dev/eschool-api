<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importe le trait

class EmploiDuTempsPdf extends Model
{
    use HasUuids; // Utilise le trait ici

    protected $table = 'emplois_du_temps_pdf';

    protected $fillable = [
        'groupe_id',
        'titre',
        'fichier_url',
        'format'
    ];

    // Accesseur pour transformer 'fichier_url' en lien cliquable pour React
    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        return asset('storage/' . $this->fichier_url);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'groupe_id');
    }
}
