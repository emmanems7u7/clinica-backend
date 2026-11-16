<?php

namespace App\Http\Requests;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'           => ['required', Rule::in(AppointmentStatus::values())],
            'cancelled_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:255'],
        ];
    }
}
