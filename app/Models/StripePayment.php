<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePayment extends Model
{
    protected $fillable = [
        'user_id',
        'user_zipcode_subscription_id',
        'zipcode_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_checkout_session_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'amount_cents',
        'currency',
        'status',
        'billing_reason',
        'billing_interval',
        'customer_email',
        'customer_name',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserZipcodeSubscription::class, 'user_zipcode_subscription_id');
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function checkoutHold(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CheckoutHold::class);
    }

    public function formattedAmount(): string
    {
        $amount = number_format($this->amount_cents / 100, 2);

        return strtoupper($this->currency).' '.$amount;
    }
}
