<?php

namespace App\Services;

use App\Models\ClinicalRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ClinicalRecordService extends BaseCrudService
{
    protected string $model = ClinicalRecord::class;
    protected array $with = ['patient', 'dentist.user'];
    protected array $filterable = ['patient_id', 'dentist_id', 'appointment_id'];

    public function create(array $data): Model
    {
        $user = auth()->user();

        // Si quien registra es odontólogo, la autoría es suya; si no (admin), debe indicarse
        $dentistId = $user->dentist?->id ?? ($data['dentist_id'] ?? null);

        if (! $dentistId) {
            throw ValidationException::withMessages(['dentist_id' => ['Debe indicar el odontólogo responsable.']]);
        }

        $data['dentist_id'] = $dentistId;

        return parent::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $this->assertCanModify($model);
        unset($data['dentist_id'], $data['patient_id']); // autoría y paciente inmutables

        return parent::update($model, $data);
    }

    public function delete(Model $model): void
    {
        $this->assertCanModify($model);
        parent::delete($model);
    }

    /** Un odontólogo solo modifica sus propios registros; admin puede todos. */
    protected function assertCanModify(ClinicalRecord $record): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return;
        }

        abort_unless($user->dentist && $user->dentist->id === $record->dentist_id, 403, 'Solo el autor puede modificar este registro clínico.');
    }
}
