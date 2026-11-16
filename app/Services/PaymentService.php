<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentService extends BaseCrudService
{
    protected string $model = Payment::class;
    protected array $with = ['patient', 'appointment'];
    protected array $searchable = ['reference'];
    protected array $filterable = ['patient_id', 'appointment_id', 'method', 'status'];

    public function create(array $data): Model
    {
        $data['status']      ??= 'paid';
        $data['currency']    ??= config('clinic.currency');
        $data['received_by']   = auth()->id();
        $data['paid_at']     ??= $data['status'] === 'paid' ? now() : null;

        return parent::create($data);
    }
}
