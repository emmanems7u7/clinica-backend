<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'start_at'         => $this->start_at?->toIso8601String(),
            'end_at'           => $this->end_at?->toIso8601String(),
            'status'           => $this->status->value,
            'reason'           => $this->reason,
            'notes'            => $this->notes,
            'cancelled_reason' => $this->cancelled_reason,
            'patient'          => new PatientSummaryResource($this->whenLoaded('patient')),
            'dentist'          => new DentistResource($this->whenLoaded('dentist')),
            'treatments'       => $this->whenLoaded('treatments', fn () => $this->treatments->map(fn ($t) => [
                'id'           => $t->id,
                'code'         => $t->code,
                'name'         => $t->name,
                'tooth_number' => $t->pivot->tooth_number,
                'price'        => $t->pivot->price,
                'notes'        => $t->pivot->notes,
            ])),
            'total'            => $this->whenLoaded('treatments', fn () => number_format((float) $this->treatments->sum('pivot.price'), 2, '.', '')),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
