<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasUuids;
    protected $guarded = [];
public function emplois()
{
    return $this->belongsToMany(
        \App\Models\Emploi::class,
        'emploi_groupe',
        'group_id',
        'emploi_id'
    );
}
    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function emploisDuTemps()
    {
        return $this->hasMany(EmploiDuTempsPdf::class, 'groupe_id');
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function stagiaireprofiles()
    {
        return $this->hasMany(StagiaireProfile::class,'groupe_id');
    }
    public function modules()
{
    return $this->belongsToMany(Module::class, 'groupe_module');
}
}
