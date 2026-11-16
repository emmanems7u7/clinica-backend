<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * CRUD genérico. Cada service concreto define el modelo, las relaciones a cargar,
 * los campos buscables (?search=) y los filtros exactos (?campo=valor).
 */
abstract class BaseCrudService
{
    /** @var class-string<Model> */
    protected string $model;
    protected array $with = [];
    protected array $searchable = [];
    protected array $filterable = [];
    protected string $orderBy = 'id';
    protected string $direction = 'desc';

    protected function query(): Builder
    {
        return $this->model::query()->with($this->with);
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function (Builder $w) use ($like) {
            foreach ($this->searchable as $column) {
                $w->orWhere($column, 'like', $like);
            }
        });
    }

    /** Hook para filtros personalizados (rangos de fecha, etc.) */
    protected function extraFilters(Builder $query, array $filters): void
    {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

        if (! empty($filters['search']) && $this->searchable) {
            $this->applySearch($query, (string) $filters['search']);
        }

        foreach ($this->filterable as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        $this->extraFilters($query, $filters);

        $direction = ($filters['order'] ?? $this->direction) === 'asc' ? 'asc' : 'desc';
        $perPage   = max(1, min((int) ($filters['per_page'] ?? 15), 100));

        return $query->orderBy($this->orderBy, $direction)->paginate($perPage)->withQueryString();
    }

    public function find(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model::create($data)->load($this->with);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh($this->with);
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
