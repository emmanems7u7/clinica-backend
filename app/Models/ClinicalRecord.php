<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalRecord extends Model
{
    use SoftDeletes;

    protected $fillable = ['patient_id', 'dentist_id', 'appointment_id', 'diagnosis', 'procedure_notes', 'prescription'];

    protected function casts(): array
    {
        return [
            'diagnosis'       => 'encrypted',
            'procedure_notes' => 'encrypted',
            'prescription'    => 'encrypted',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
