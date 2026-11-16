<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'first_name'              => $this->first_name,
            'last_name'               => $this->last_name,
            'full_name'               => $this->full_name,
            'document_number'         => $this->document_number,
            'birth_date'              => $this->birth_date?->toDateString(),
            'age'                     => $this->birth_date?->age,
            'gender'                  => $this->gender,
            'phone'                   => $this->phone,
            'email'                   => $this->email,
            'address'                 => $this->address,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'allergies'               => $this->allergies,
            'medical_history'         => $this->medical_history,
            'notes'                   => $this->notes,
            'created_at'              => $this->created_at?->toIso8601String(),
        ];
    }
}
