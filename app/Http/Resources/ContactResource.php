<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Solo expone los campos esenciales del contacto.
     * Oculta: timestamps, email_verified_at, imageable_type, imageable_id, etc.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'nombre'  => $this->nombre,
            'numero'  => $this->numero,
            'usuario' => $this->usuario,
            'correo'  => $this->correo,
            'images'  => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => [
                    'id'  => $img->id,
                    'url' => $img->url,
                    'alt' => $img->alt,
                ]);
            }),
        ];
    }
}
