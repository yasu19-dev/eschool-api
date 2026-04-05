<?php

namespace App\Http\Resources\Stagiaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsenceResource extends JsonResource
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
        'date' => $this->seance->date,
        'module' => $this->seance->module->nom,
        // Traduction de la logique métier
        'type' => $this->est_en_retard ? 'Retard' : 'Absence',
        'est_justifie' => (bool)$this->est_justifie,
        'motif' => $this->motif,
    ];
}
}
