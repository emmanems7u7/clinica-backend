<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DentistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'license_number' => $this->license_number,
            'specialty'      => $this->specialty,
            'phone'          => $this->phone,
            'is_active'      => $this->is_active,
            'user'           => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
        ];
    }
}
