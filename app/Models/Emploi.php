<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emploi extends Model {
protected $fillable = ['titre', 'file_path'];

    public function groupes()
{
    // On précise : Table pivot, Clé étrangère 1 (emploi_id), Clé étrangère 2 (group_id)
    return $this->belongsToMany(
        \App\Models\Groupe::class,
        'emploi_groupe',
        'emploi_id',
        'group_id'
    );
}
}
