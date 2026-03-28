<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
