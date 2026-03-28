<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
   use HasUuids;

    // Le nom de la table correspondant à la migration qu'on a créée
    protected $table = 'seances';
    protected $guarded = [];

    public function formateur()
    {
        return $this->belongsTo(FormateurProfile::class, 'formateur_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    // Pour afficher la liste des étudiants absents d'une séance
    public function presences()
    {
        return $this->hasMany(Presence::class, 'seance_id');
    }
    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'seance_id');
    }
}
