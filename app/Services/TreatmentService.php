<?php

namespace App\Services;

use App\Models\Treatment;

class TreatmentService extends BaseCrudService
{
    protected string $model = Treatment::class;
    protected array $searchable = ['code', 'name'];
    protected array $filterable = ['is_active'];
    protected string $orderBy = 'name';
    protected string $direction = 'asc';
}
