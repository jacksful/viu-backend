<?php

namespace App\Models;

use App\Models\CheckoutHold;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Waitlist extends Model
{
    public const LOCK_HOURS = 96;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'zip_code',
        'message',
        'status',
        'zip_available_notice_sent_for_subscription_id',
        'zip_available_notice_sent_for_hold_id',
        'converted_to_user_id',
        'converted_at',
        'stripe_payment_id',
        'checkout_url',
        'locked_until',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'converted_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function convertedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_to_user_id');
    }

    public function stripePayment(): BelongsTo
    {
        return $this->belongsTo(StripePayment::class);
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class, 'zip_code', 'code');
    }

    public function scopeActiveLock(Builder $query): Builder
    {
        return $query
            ->whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->whereHas('stripePayment', fn (Builder $paymentQuery) => $paymentQuery->where('status', 'checkout_pending'));
    }

    public static function isZipcodeLocked(string $zipCode, ?int $exceptWaitlistId = null): bool
    {
        if (CheckoutHold::isZipcodeCodeHeld($zipCode)) {
            return true;
        }

        $query = static::query()
            ->where('zip_code', $zipCode)
            ->activeLock();

        if ($exceptWaitlistId) {
            $query->where('id', '!=', $exceptWaitlistId);
        }

        return $query->exists();
    }

    public function hasActiveLock(): bool
    {
        return $this->locked_until !== null
            && $this->locked_until->isFuture()
            && $this->stripePayment?->status === 'checkout_pending';
    }

    public function canShowConvertAction(): bool
    {
        return is_null($this->converted_to_user_id) && ! $this->hasActiveLock();
    }

    /**
     * @return list<string>
     */
    public function conversionBlockers(): array
    {
        $blockers = [];

        if ($this->converted_to_user_id) {
            $blockers[] = 'Already converted to a client.';
        }

        if ($this->hasActiveLock()) {
            $blockers[] = 'A checkout link was already sent and the ZIP is still locked.';
        }

        if (blank($this->email) || blank($this->name) || blank($this->zip_code)) {
            $blockers[] = 'Name, email, and ZIP code are required.';
        }

        if (! app(\App\Services\StripeService::class)->isEnabled()) {
            $blockers[] = 'Stripe checkout is not configured.';
        }

        $zipcode = Zipcode::query()
            ->where('code', $this->zip_code)
            ->where('is_active', true)
            ->first();

        if (! $zipcode) {
            $blockers[] = 'ZIP code is not in the active coverage area.';

            return $blockers;
        }

        if (! $zipcode->hasPurchasablePlans()) {
            $blockers[] = 'Stripe pricing is not configured for this ZIP.';
        }

        if (! $zipcode->hasDatasets()) {
            $blockers[] = 'Property data is not available for this ZIP yet.';
        }

        if (UserZipcodeSubscription::active()->forZipcode($zipcode->id)->exists()) {
            $blockers[] = 'This ZIP is still owned by another agent.';
        }

        if (static::isZipcodeLocked($this->zip_code, $this->id)) {
            $blockers[] = 'This ZIP is locked for another waitlist checkout.';
        }

        return $blockers;
    }

    public function isReadyToConvert(): bool
    {
        return $this->canShowConvertAction() && $this->conversionBlockers() === [];
    }
}
