<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
   use HasUuids;
    protected $fillable = [
        'stagiaire_id',
        'module_id',
        'formateur_id', // Ajoute-le si tu veux savoir quel prof a mis la note
        'valeur',
        'type_evaluation',
        'session'
    ];

    public function stagiaire()
    {
        return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function formateur()
    {
        return $this->belongsTo(FormateurProfile::class, 'formateur_id');
    }
}
