<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma el modelo en un array JSON limpio y seguro para el frontend.
     * No expone datos sensibles: sin password, sin remember_token, sin timestamps internos.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'images' => $this->whenLoaded('images', function () {
                $image = $this->images->first();
                return $image ? $image->url : null;
            }),
        ];
    }
}
