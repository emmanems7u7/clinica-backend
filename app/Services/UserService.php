<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService extends BaseCrudService
{
    protected string $model = User::class;
    protected array $with = ['roles'];
    protected array $searchable = ['name', 'email'];
    protected array $filterable = ['is_active'];
    protected string $orderBy = 'name';
    protected string $direction = 'asc';

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $roles = Arr::pull($data, 'roles', []);
            $user  = User::create($data);
            $user->syncRoles($roles);

            return $user->load($this->with);
        });
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $roles = Arr::pull($data, 'roles');

            if (isset($data['is_active']) && ! $data['is_active'] && $model->is(auth()->user())) {
                throw ValidationException::withMessages(['is_active' => ['No puede desactivar su propio usuario.']]);
            }

            $model->update($data);

            if ($roles !== null) {
                $model->syncRoles($roles);
            }
            if (isset($data['is_active']) && ! $data['is_active']) {
                $model->tokens()->delete();
            }

            return $model->fresh($this->with);
        });
    }

    /** Los usuarios no se eliminan (auditoría): se desactivan y se revocan sus tokens. */
    public function delete(Model $model): void
    {
        if ($model->is(auth()->user())) {
            throw ValidationException::withMessages(['user' => ['No puede desactivar su propio usuario.']]);
        }

        $model->update(['is_active' => false]);
        $model->tokens()->delete();
    }
}
