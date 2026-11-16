<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Treatment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService extends BaseCrudService
{
    use Concerns\ScopesByDentist;

    protected string $model = Appointment::class;
    protected array $with = ['patient', 'dentist.user', 'treatments'];
    protected array $filterable = ['dentist_id', 'patient_id', 'status'];
    protected string $orderBy = 'start_at';

    protected function query(): Builder
    {
        return $this->scopeToDentist(parent::query());
    }

    protected function extraFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('start_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('start_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $treatments = Arr::pull($data, 'treatments', []);

            $this->ensureAvailable((int) $data['dentist_id'], $data['start_at'], $data['end_at']);

            $data['created_by'] = auth()->id();
            $data['status']   ??= AppointmentStatus::Scheduled->value;

            $appointment = Appointment::create($data);
            $this->syncTreatments($appointment, $treatments);

            return $appointment->load($this->with);
        });
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $treatments = Arr::pull($data, 'treatments');

            if (Arr::hasAny($data, ['dentist_id', 'start_at', 'end_at'])) {
                $this->ensureAvailable(
                    (int) ($data['dentist_id'] ?? $model->dentist_id),
                    $data['start_at'] ?? $model->start_at,
                    $data['end_at'] ?? $model->end_at,
                    $model->id,
                );
            }

            $model->update($data);

            if ($treatments !== null) {
                $this->syncTreatments($model, $treatments);
            }

            return $model->fresh($this->with);
        });
    }

    public function changeStatus(Appointment $appointment, string $status, ?string $reason = null): Appointment
    {
        if (in_array($appointment->status->value, AppointmentStatus::final(), true)) {
            throw ValidationException::withMessages([
                'status' => ['La cita ya está finalizada y no admite cambios de estado.'],
            ]);
        }

        $appointment->update([
            'status'           => $status,
            'cancelled_reason' => $status === AppointmentStatus::Cancelled->value ? $reason : $appointment->cancelled_reason,
        ]);

        return $appointment->fresh($this->with);
    }

    /** Valida horario coherente y que el odontólogo no tenga otra cita solapada. */
    protected function ensureAvailable(int $dentistId, mixed $start, mixed $end, ?int $ignoreId = null): void
    {
        $start = Carbon::parse($start);
        $end   = Carbon::parse($end);

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['end_at' => ['La hora de fin debe ser posterior al inicio.']]);
        }

        // Bloqueo para evitar doble reserva simultánea del mismo odontólogo
        Dentist::whereKey($dentistId)->lockForUpdate()->first();

        $conflict = Appointment::where('dentist_id', $dentistId)
            ->whereNotIn('status', AppointmentStatus::freeing())
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_at' => ['El odontólogo ya tiene una cita en ese horario.'],
            ]);
        }
    }

    protected function syncTreatments(Appointment $appointment, array $rows): void
    {
        $appointment->treatments()->detach();

        foreach ($rows as $row) {
            $treatment = Treatment::findOrFail($row['treatment_id']);

            $appointment->treatments()->attach($treatment->id, [
                'tooth_number' => $row['tooth_number'] ?? null,
                'price'        => $row['price'] ?? $treatment->price,
                'notes'        => $row['notes'] ?? null,
            ]);
        }
    }
}
