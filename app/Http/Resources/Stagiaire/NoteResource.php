<?php

namespace App\Http\Resources\Stagiaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
        'module' => $this->module->nom ?? 'N/A',
        'note' => $this->valeur,
        'coefficient' => $this->module->coefficient,
        'type_evaluation' => $this->type_evaluation,
        'session' => $this->session,
        'date_saisie' => $this->created_at->format('d/m/Y'),
    ];
}
}
