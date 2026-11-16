<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Treatment extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'price', 'duration_minutes', 'is_active'];

    protected function casts(): array
    {
        return [
            'price'            => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active'        => 'boolean',
        ];
    }
}
