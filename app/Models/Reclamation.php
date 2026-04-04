<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Reclamation extends Model
{
    use HasUuids;
protected $fillable = [
        'stagiaire_id',
        'type',
        'message',
        'statut'
    ];

    public function stagiaire() { return $this->belongsTo(StagiaireProfile::class, 'stagiaire_id'); }

    public function messages() { return $this->hasMany(ReclamationMessage::class); }
}
