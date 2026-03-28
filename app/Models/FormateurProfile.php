<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormateurProfile extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'formateur_id');
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class, 'formateur_id');
    }

    public function notesSaisies()
    {
        return $this->hasMany(Note::class, 'formateur_id');
    }
}
