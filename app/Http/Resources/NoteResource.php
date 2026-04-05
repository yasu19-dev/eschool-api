<?php

namespace App\Http\Resources;

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
        'stagiaire' => $this->stagiaire->nom . ' ' . $this->stagiaire->prenom,
        'valeur' => $this->valeur,
        'type_evaluation' => $this->type_evaluation,
        'session' => $this->session,
        'session' => $this->session,
        'module' => $this->module->nom,
    ];
}
}
