<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarSection extends Model
{
    use HasTranslations;

    protected $fillable = ['car_id', 'label', 'title', 'text', 'image', 'items', 'sort'];

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'title' => 'array',
            'text' => 'array',
            'items' => 'array',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
