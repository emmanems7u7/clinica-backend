<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'description'      => $this->description,
            'price'            => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'is_active'        => $this->is_active,
        ];
    }
}
