<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'title', 'body', 'meta_description', 'attachment', 'is_published'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'body' => 'array',
            'meta_description' => 'array',
            'is_published' => 'boolean',
        ];
    }
}
