<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $r = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'code'             => [$r, 'string', 'max:30', Rule::unique('treatments', 'code')->ignore($this->route('id'))],
            'name'             => [$r, 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:5000'],
            'price'            => [$r, 'numeric', 'min:0', 'max:99999999.99'],
            'duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:600'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
