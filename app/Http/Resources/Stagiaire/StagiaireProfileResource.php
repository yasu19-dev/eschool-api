<?php

namespace App\Http\Resources\Stagiaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StagiaireProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'nom' => $this->nom,
        'prenom' => $this->prenom,
        'cef' => $this->cef,
        'cin' => $this->cin,
        'date_naissance' => $this->date_naissance,
        'lieu_naissance' => $this->lieu_naissance,
        'adresse' => $this->adresse,
        'telephone' => $this->telephone,
        'photo' => $this->photo_url,
        'annee_scolaire' => $this->annee_scolaire,
        'debug_id' => $this->groupe_id, // À supprimer, juste pour debug
        'groupe' => $this->groupe->code ?? 'N/A',
        'email' => $this->user->email,
    ];
}
}
