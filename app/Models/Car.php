<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasTranslations;

    protected $fillable = [
        'car_category_id', 'slug', 'name', 'tagline', 'excerpt', 'description', 'price_from',
        'card_image', 'hero_image', 'hero_image_mobile', 'brochure', 'tour_url',
        'colors', 'gallery', 'specs', 'meta_title', 'meta_description',
        'is_published', 'is_featured', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'tagline' => 'array',
            'excerpt' => 'array',
            'description' => 'array',
            'colors' => 'array',
            'gallery' => 'array',
            'specs' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CarCategory::class, 'car_category_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CarSection::class)->orderBy('sort');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort');
    }
}
