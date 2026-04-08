<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StagiaireProfile extends Model
{
    use HasUuids;
    // * les champs suivants sont "fillable" (assignables en masse) pour la création et la mise à jour
    protected $fillable = [
        'id',
        'user_id',
        'cef',
        'cin',
        'nom',
        'prenom',
        'groupe_id',
        'date_naissance',
        'lieu_naissance',
        'adresse',
        'telephone',
        'annee_scolaire'
    ];
   public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relations pédagogiques
    public function notes()
    {
        return $this->hasMany(Note::class, 'stagiaire_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'stagiaire_id');
    }

    // Démarches
    public function demandesAttestations()
    {
        return $this->hasMany(DemandeAttestation::class, 'stagiaire_id');
    }

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'stagiaire_id');
    }

    public function groupes()
    {
        return $this->belongsTo(Groupe::class, 'groupe_stagiaire', 'stagiaire_id', 'groupe_id');
    }
    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class, 'stagiaire_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'stagiaire_id');
    }


}
