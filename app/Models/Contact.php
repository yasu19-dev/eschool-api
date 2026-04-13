<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Pour gérer les UUID automatiquement
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, HasUuids;

    /**
     * La table associée au modèle.
     * @var string
     */
    protected $table = 'contacts';

    /**
     * Les attributs qui sont assignables en masse.
     * @var array
     */
    protected $fillable = [
        'nom',
        'email',
        'telephone',
        'sujet',
        'message',
        'lu', // Par défaut false en base
    ];

    /**
     * On précise que l'ID n'est pas un entier auto-incrémenté.
     */
    public $incrementing = false;
    protected $keyType = 'string';
}
