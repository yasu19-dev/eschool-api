<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function formateur() { return $this->belongsTo(FormateurProfile::class, 'formateur_id'); }
    public function groupe() { return $this->belongsTo(Groupe::class); }
}
