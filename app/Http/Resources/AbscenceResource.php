<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbscenceResource extends JsonResource
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
        'stagiaire' => $this->stagiaire->nom . ' ' . $this->stagiaire->prenom,
        'groupe' => $this->stagiaire->groupe->code,
        'date' => $this->seance->date,
        'creneau' => $this->seance->creneau,
        'type' => $this->est_en_retard ? 'Retard' : 'Absence',
        'justifie' => (bool)$this->est_justifie,
        'motif' => $this->motif,
    ];
}
}
