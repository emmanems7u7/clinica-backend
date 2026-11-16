<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

trait SeedsSafely
{
    /** @var array<string, string[]> */
    private static array $columnCache = [];

    protected function columns(string $table): array
    {
        return self::$columnCache[$table] ??= Schema::getColumnListing($table);
    }

    protected function hasColumn(string $model, string $column): bool
    {
        return in_array($column, $this->columns((new $model)->getTable()), true);
    }

    /**
     * Crea un registro ignorando los datos cuya columna no exista en la tabla.
     * Así el seeder sigue funcionando si su migración no tiene algún campo opcional.
     */
    protected function make(string $model, array $data): Model
    {
        $cols = $this->columns((new $model)->getTable());

        return $model::forceCreate(array_intersect_key($data, array_flip($cols)));
    }

    /** Asigna el primer rol que exista entre los nombres candidatos (Spatie). */
    protected function assignRole($user, array $candidates): void
    {
        $roleClass = \Spatie\Permission\Models\Role::class;
        foreach ($candidates as $name) {
            if (class_exists($roleClass) && $roleClass::where('name', $name)->exists()) {
                $user->assignRole($name);

                return;
            }
        }
        $this->command?->warn('No existe ninguno de los roles [' . implode(', ', $candidates) . ']. Ejecute primero el seeder de roles.');
    }
}
