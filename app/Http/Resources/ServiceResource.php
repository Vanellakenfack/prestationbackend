<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'prix' => $this->prix,
            'categorie' => $this->categorie,
            'localisation' => $this->localisation,
            'duree' => $this->duree_minutes,
            'statut' => $this->is_published ? 'Publié' : 'Brouillon',
            'prestataire' => [
                'id' => $this->prestataire->id,
                'nom' => $this->prestataire->name,
                'email' => $this->prestataire->email
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}