<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Solo expone los campos públicos de la imagen.
     * Oculta: imageable_id, imageable_type, timestamps.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'  => $this->id,
            'url' => $this->url,
            'alt' => $this->alt,
        ];
    }
}
