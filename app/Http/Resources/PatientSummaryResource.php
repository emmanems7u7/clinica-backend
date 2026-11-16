<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Versión mínima del paciente para embeber en citas, pagos e historias (sin datos médicos). */
class PatientSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'full_name'       => $this->full_name,
            'document_number' => $this->document_number,
            'phone'           => $this->phone,
        ];
    }
}
