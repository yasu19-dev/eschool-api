<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'institution_id', 'title', 'specialite', 'code',
        'duration', 'niveau', 'description', 'modules',
        'debouches', 'color'
    ];

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
