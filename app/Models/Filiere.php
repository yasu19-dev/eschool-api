<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory, HasUuids;

    // Autorise le remplissage de toutes les colonnes
    protected $guarded = [];

    // Très important pour React : convertit automatiquement le JSON de la base de données en tableau
    protected $casts = [
        'modules' => 'array',
        'debouches' => 'array',
    ];

    // Relation avec l'établissement (Institution)
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
