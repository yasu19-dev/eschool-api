<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploiDuTempsPdf extends Model
{
    use HasUuids;

    protected $table = 'emplois_du_temps_pdf';

    protected $fillable = [
        'groupe_id',
        'titre',
        'fichier_url',
        'format'
    ];

    protected $appends = ['full_url'];

    public function getFullUrlAttribute() {
        return asset('storage/' . $this->fichier_url);
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class, 'groupe_id');
    }
}
