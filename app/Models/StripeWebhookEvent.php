<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeWebhookEvent extends Model
{
    protected $fillable = [
        'stripe_event_id',
        'type',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public static function alreadyProcessed(string $eventId): bool
    {
        return static::query()->where('stripe_event_id', $eventId)->exists();
    }

    public static function markProcessed(string $eventId, string $type): self
    {
        return static::query()->create([
            'stripe_event_id' => $eventId,
            'type' => $type,
            'processed_at' => now(),
        ]);
    }
}
