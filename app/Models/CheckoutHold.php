<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutHold extends Model
{
    public const HOLD_DAYS = 4;

    public const EXTEND_HOURS = 24;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_RELEASED = 'released';

    public const RECOVERY_STATUS_SENT = 'sent';

    public const RECOVERY_STATUS_FAILED = 'failed';

    protected $fillable = [
        'stripe_payment_id',
        'zipcode_id',
        'waitlist_id',
        'status',
        'checkout_started_at',
        'hold_expires_at',
        'released_at',
        'release_reason',
        'recovery_email_sent_at',
        'recovery_email_status',
        'recovery_email_error',
    ];

    protected $casts = [
        'checkout_started_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'released_at' => 'datetime',
        'recovery_email_sent_at' => 'datetime',
    ];

    public function stripePayment(): BelongsTo
    {
        return $this->belongsTo(StripePayment::class);
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function waitlist(): BelongsTo
    {
        return $this->belongsTo(Waitlist::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where('hold_expires_at', '>', now());
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('status', '!=', self::STATUS_ACTIVE)
                ->orWhere('hold_expires_at', '<=', now());
        });
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->hold_expires_at !== null
            && $this->hold_expires_at->isFuture();
    }

    public function isExpiringSoon(int $hours = 24): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->hold_expires_at->lte(now()->addHours($hours));
    }

    public static function isZipcodeHeld(int $zipcodeId, ?int $exceptHoldId = null): bool
    {
        $query = static::query()
            ->where('zipcode_id', $zipcodeId)
            ->active();

        if ($exceptHoldId) {
            $query->where('id', '!=', $exceptHoldId);
        }

        return $query->exists();
    }

    public static function isZipcodeCodeHeld(string $zipCode, ?int $exceptHoldId = null): bool
    {
        $zipcode = Zipcode::query()->where('code', $zipCode)->first();

        if (! $zipcode) {
            return false;
        }

        return static::isZipcodeHeld($zipcode->id, $exceptHoldId);
    }

    public function formattedPlanLabel(): string
    {
        $payment = $this->stripePayment;

        if (! $payment) {
            return '—';
        }

        $interval = match ($payment->billing_interval) {
            Zipcode::BILLING_YEARLY => 'Yearly',
            Zipcode::BILLING_MONTHLY => 'Monthly',
            default => 'Plan',
        };

        return $interval.' • $'.number_format($payment->amount_cents / 100, 2);
    }

    public function stripeDashboardUrl(): ?string
    {
        $sessionId = $this->stripePayment?->stripe_checkout_session_id;

        if (blank($sessionId)) {
            return null;
        }

        $settings = StripeSetting::singleton();
        $prefix = $settings->test_mode ? 'test/' : '';

        return "https://dashboard.stripe.com/{$prefix}checkout/sessions/{$sessionId}";
    }
}
