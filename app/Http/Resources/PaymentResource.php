<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'appointment_id' => $this->appointment_id,
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'method'         => $this->method,
            'status'         => $this->status,
            'reference'      => $this->reference,
            'paid_at'        => $this->paid_at?->toIso8601String(),
            'notes'          => $this->notes,
            'patient'        => new PatientSummaryResource($this->whenLoaded('patient')),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
