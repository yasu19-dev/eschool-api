<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasUuids;
    protected $guarded = [];

    // Très important pour React : convertit automatiquement le JSON en tableau
    protected $casts = [
        'modules' => 'array',
        'debouches' => 'array',
    ];

    public function institution() { return $this->belongsTo(Institution::class); }
}
