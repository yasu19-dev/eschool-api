<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function staffMembres() { return $this->hasMany(StaffMembre::class); }
    public function filieres() { return $this->hasMany(Filiere::class); }
    public function localisations() { return $this->hasMany(Localisation::class); }
    
}
