<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'type', 'status', 'car_id', 'dealer_id', 'name', 'phone', 'email', 'city', 'subject',
        'payment_method', 'preferred_date', 'message', 'manager_note', 'locale', 'page_url', 'ip', 'utm',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeadType::class,
            'status' => LeadStatus::class,
            'preferred_date' => 'date',
            'utm' => 'array',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
