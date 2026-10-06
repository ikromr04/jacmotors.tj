<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarCategory extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'name', 'sort'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class);
    }
}
