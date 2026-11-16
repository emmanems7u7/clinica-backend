<?php

namespace App\Services;

use App\Models\Dentist;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DentistService extends BaseCrudService
{
    protected string $model = Dentist::class;
    protected array $with = ['user'];
    protected array $filterable = ['is_active'];

    protected function applySearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '%_\\').'%';

        $query->where(function (Builder $w) use ($like) {
            $w->where('license_number', 'like', $like)
                ->orWhere('specialty', 'like', $like)
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
        });
    }

    /** Crea el usuario de acceso + el perfil de odontólogo con rol "dentist". */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $user = User::create(Arr::only($data, ['name', 'email', 'phone', 'password']));
            $user->assignRole('dentist');

            $dentist = Dentist::create([
                'user_id'        => $user->id,
                'license_number' => $data['license_number'],
                'specialty'      => $data['specialty'] ?? null,
                'phone'          => $data['phone'] ?? null,
                'is_active'      => $data['is_active'] ?? true,
            ]);

            return $dentist->load($this->with);
        });
    }

    public function update(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $userData = Arr::only($data, ['name', 'email', 'phone', 'password']);
            if (isset($data['is_active'])) {
                $userData['is_active'] = $data['is_active'];
            }
            if ($userData) {
                $model->user->update($userData);
            }
            $model->update(Arr::only($data, ['license_number', 'specialty', 'phone', 'is_active']));

            if (isset($data['is_active']) && ! $data['is_active']) {
                $model->user->tokens()->delete();
            }

            return $model->fresh($this->with);
        });
    }

    /** Un odontólogo con historial no se borra: se desactiva y se revocan sus tokens. */
    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $model->update(['is_active' => false]);
            $model->user->update(['is_active' => false]);
            $model->user->tokens()->delete();
        });
    }
}
