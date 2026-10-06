<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    use HasTranslations;

    protected $fillable = [
        'car_id', 'title', 'subtitle', 'button_text', 'link', 'image', 'image_mobile', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'button_text' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function url(): ?string
    {
        return $this->car ? route('cars.show', $this->car) : $this->link;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort');
    }
}
