<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zipcode extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'city',
        'state',
        'area',
        'monthly_price',
        'yearly_price',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_monthly_price_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public const BILLING_MONTHLY = 'month';

    public const BILLING_YEARLY = 'year';

    /**
     * @return list<array{interval: string, label: string, amount: float, amount_cents: int, suffix: string}>
     */
    public function billingPlans(): array
    {
        $plans = [];

        if ($this->monthlyPriceCents() > 0) {
            $plans[] = [
                'interval' => self::BILLING_MONTHLY,
                'label' => 'Monthly',
                'amount' => (float) $this->monthly_price,
                'amount_cents' => $this->monthlyPriceCents(),
                'suffix' => '/mo',
            ];
        }

        if ($this->yearlyPriceCents() > 0) {
            $plans[] = [
                'interval' => self::BILLING_YEARLY,
                'label' => 'Yearly',
                'amount' => (float) $this->yearly_price,
                'amount_cents' => $this->yearlyPriceCents(),
                'suffix' => '/yr',
            ];
        }

        return $plans;
    }

    /**
     * @return array{interval: string, label: string, amount: float, amount_cents: int, suffix: string, stripe_interval: string}
     */
    public function resolveBillingPlan(string $interval): array
    {
        $interval = $interval === self::BILLING_YEARLY ? self::BILLING_YEARLY : self::BILLING_MONTHLY;

        foreach ($this->billingPlans() as $plan) {
            if ($plan['interval'] === $interval) {
                return [
                    ...$plan,
                    'stripe_interval' => $interval,
                ];
            }
        }

        throw new \InvalidArgumentException("The selected billing interval is not available for ZIP {$this->code}.");
    }

    public function monthlyPriceCents(): int
    {
        return (int) round(((float) ($this->monthly_price ?? 0)) * 100);
    }

    public function yearlyPriceCents(): int
    {
        return (int) round(((float) ($this->yearly_price ?? 0)) * 100);
    }

    public function hasBillingPlans(): bool
    {
        return $this->billingPlans() !== [];
    }

    public function stripePriceIdForInterval(string $interval): ?string
    {
        return $interval === self::BILLING_YEARLY
            ? $this->stripe_price_id
            : $this->stripe_monthly_price_id;
    }

    public function hasStripePriceForInterval(string $interval): bool
    {
        return filled($this->stripePriceIdForInterval($interval));
    }

    /**
     * Billing plans that have a synced Stripe price and can be purchased online.
     *
     * @return list<array{interval: string, label: string, amount: float, amount_cents: int, suffix: string, stripe_price_id: string}>
     */
    public function purchasableBillingPlans(): array
    {
        $plans = [];

        foreach ($this->billingPlans() as $plan) {
            $stripePriceId = $this->stripePriceIdForInterval($plan['interval']);

            if (! filled($stripePriceId)) {
                continue;
            }

            $plans[] = [
                ...$plan,
                'stripe_price_id' => $stripePriceId,
            ];
        }

        return $plans;
    }

    public function hasPurchasablePlans(): bool
    {
        return $this->purchasableBillingPlans() !== [];
    }

    public function hasDatasets(): bool
    {
        return $this->datasets()->exists();
    }

    public function isReadyToPurchase(): bool
    {
        return $this->hasDatasets() && $this->hasPurchasablePlans();
    }

    public function assertStripePriceForInterval(string $interval): string
    {
        $stripePriceId = $this->stripePriceIdForInterval($interval);

        if (! filled($stripePriceId)) {
            throw new \RuntimeException("Stripe pricing is not configured for ZIP {$this->code}. An admin must create Stripe prices before this territory can be purchased.");
        }

        return $stripePriceId;
    }

    /**
     * Get all uploaded zipcodes for this zipcode.
     */
    public function uploadedZipcodes()
    {
        return $this->hasMany(UploadedZipcode::class);
    }

    /**
     * Get all datasets through uploaded zipcodes.
     */
    public function datasets()
    {
        return $this->hasManyThrough(Dataset::class, UploadedZipcode::class, 'zipcode_id', 'uploaded_zipcode_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserZipcodeSubscription::class);
    }

    /**
     * Get all leads associated with this zipcode.
     */
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_zipcode')
            ->withTimestamps();
    }
}
