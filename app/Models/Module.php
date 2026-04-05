<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasUuids;
    protected $fillable = ['code', 'intitule', 'coefficient', 'masse_horaire'];

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function groupes()
{
    return $this->belongsToMany(Groupe::class, 'groupe_module');
}
}
