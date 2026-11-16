<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesByDentist
{
    /**
     * Un usuario que SOLO es odontólogo ve únicamente sus propias citas.
     * Admin y recepción ven todas.
     */
    protected function scopeToDentist(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user && $user->hasRole('dentist') && ! $user->hasAnyRole(['admin', 'receptionist'])) {
            $query->where($query->getModel()->getTable().'.dentist_id', $user->dentist?->id ?? 0);
        }

        return $query;
    }
}
