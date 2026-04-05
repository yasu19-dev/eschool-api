<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
{
    // On récupère le profil lié (admin, formateur ou stagiaire)
    $profile = $this->getProfileAttribute();

    return [
        'id' => $this->id,
        'email' => $this->email,
        'role' => $this->role,
        'etat' => $this->etat,
        'nom' => $profile->nom ?? 'N/A',
        'prenom' => $profile->prenom ?? 'N/A',
        // Si c'est un admin, on précise s'il est directeur ou responsable
        'fonction' => $this->role === 'admin' ? $this->adminProfile->role_admin : $this->role,
        'derniere_connexion' => $this->lastLogin ? $this->lastLogin->format('d/m/Y H:i') : 'Jamais',
    ];
}
}
