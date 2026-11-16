<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'appointment_id'  => $this->appointment_id,
            'diagnosis'       => $this->diagnosis,
            'procedure_notes' => $this->procedure_notes,
            'prescription'    => $this->prescription,
            'patient'         => new PatientSummaryResource($this->whenLoaded('patient')),
            'dentist'         => new DentistResource($this->whenLoaded('dentist')),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
