<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasUuids;
   protected $fillable = [
        'formateur_id',
        'groupe_id',
        'titre',
        'contenu',
        'type' // Ajout de la colonne manquante
    ];

    public function formateur() { return $this->belongsTo(FormateurProfile::class, 'formateur_id'); }
    public function groupe() { return $this->belongsTo(Groupe::class); }
}
