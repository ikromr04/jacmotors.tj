<?php

namespace App\Models;

use App\Enums\PostType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasTranslations;

    protected $fillable = [
        'type', 'slug', 'title', 'excerpt', 'body', 'image', 'published_at', 'ends_at', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'title' => 'array',
            'excerpt' => 'array',
            'body' => 'array',
            'published_at' => 'datetime',
            'ends_at' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function url(): string
    {
        return route($this->type === PostType::Promotion ? 'promotions.show' : 'news.show', $this);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at');
    }

    #[Scope]
    protected function ofType(Builder $query, PostType $type): void
    {
        $query->where('type', $type);
    }
}
