<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name', 'city', 'address', 'working_hours', 'phone', 'email', 'lat', 'lng', 'map_embed',
        'has_sales', 'has_service', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'city' => 'array',
            'address' => 'array',
            'working_hours' => 'array',
            'has_sales' => 'boolean',
            'has_service' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort');
    }
}
