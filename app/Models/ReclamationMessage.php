<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReclamationMessage extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function reclamation() { return $this->belongsTo(Reclamation::class); }

    public function auteur() { return $this->belongsTo(User::class, 'auteur_user_id'); }
}
