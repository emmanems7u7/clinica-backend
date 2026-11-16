<?php

namespace App\Services;

use App\Models\Patient;

class PatientService extends BaseCrudService
{
    protected string $model = Patient::class;
    protected array $searchable = ['first_name', 'last_name', 'document_number', 'phone', 'email'];
    protected array $filterable = ['gender'];
    protected string $orderBy = 'last_name';
    protected string $direction = 'asc';
}
