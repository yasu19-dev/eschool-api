<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'date' => $this->date,
        'creneau' => $this->creneau, // Ex: "08:30-10:50"
        'salle' => $this->salle,
        'type' => $this->type,
        'module' => $this->module->nom,
        'groupe' => $this->groupe->code,
        'commentaire' => $this->commentaire_prof,
        // Liste des stagiaires pour faire l'appel
        'stagiaires' => $this->groupe->stagiaires->map(fn($s) => [
            'id' => $s->id,
            'nom_complet' => $s->nom . ' ' . $s->prenom
        ]),
    ];
}
}
