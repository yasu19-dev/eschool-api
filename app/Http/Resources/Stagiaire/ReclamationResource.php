<?php

namespace App\Http\Resources\Stagiaire;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReclamationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'message' => $this->message,
            'statut' => $this->statut,
            'cree_le' => $this->created_at->diffForHumans(),
        ];
    }
}
