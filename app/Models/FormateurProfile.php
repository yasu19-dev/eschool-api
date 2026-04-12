<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormateurProfile extends Model
{
    use HasUuids;
    protected $fillable = [
    'user_id',
    'matricule',
    'nom',
    'prenom',
    // --- VÉRIFIE QUE CES LIGNES SONT LÀ ---
    'adresse',
    'email_professionnel',
    'telephone',
    'bio',
    'photo_url',
    // --------------------------------------
    'specialite',
    'grade',
    'departement',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'formateur_id');
    }
    public function groupes()
    {
    // On utilise la table 'seances' comme une table pivot pour lier Formateur et Groupe
    return $this->belongsToMany(Groupe::class, 'seances', 'formateur_id', 'groupe_id')
                ->distinct(); // Pour ne pas avoir 10 fois le même groupe
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
