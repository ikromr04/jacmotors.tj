<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasTranslations;

    protected $fillable = ['type', 'car_id', 'title', 'image', 'file', 'sort'];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'title' => 'array',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
