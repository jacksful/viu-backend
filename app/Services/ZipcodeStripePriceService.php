<?php

namespace App\Services;

use App\Models\Zipcode;

class ZipcodeStripePriceService
{
    public function __construct(
        protected StripeService $stripe,
    ) {}

    /**
     * Create or sync Stripe product and price objects for a zipcode territory.
     *
     * @return array{product_id: string, yearly_price_id: ?string, monthly_price_id: ?string}
     */
    public function syncPrices(Zipcode $zipcode): array
    {
        if (! $this->stripe->isEnabled()) {
            throw new \RuntimeException('Stripe payments are not enabled. Configure Stripe in admin settings first.');
        }

        $settings = $this->stripe->settings();
        $currency = strtolower($settings->currency ?: 'usd');
        $client = $this->stripe->client();

        $productId = $zipcode->stripe_product_id;

        if ($productId) {
            $client->products->update($productId, [
                'name' => $this->productName($zipcode),
                'description' => $this->productDescription($zipcode),
                'active' => (bool) $zipcode->is_active,
                'metadata' => [
                    'zipcode_id' => (string) $zipcode->id,
                    'zipcode_code' => $zipcode->code,
                ],
            ]);
        } else {
            $product = $client->products->create([
                'name' => $this->productName($zipcode),
                'description' => $this->productDescription($zipcode),
                'active' => (bool) $zipcode->is_active,
                'metadata' => [
                    'zipcode_id' => (string) $zipcode->id,
                    'zipcode_code' => $zipcode->code,
                ],
            ]);

            $productId = $product->id;
        }

        $yearlyPriceId = $this->syncIntervalPrice(
            $zipcode,
            $productId,
            Zipcode::BILLING_YEARLY,
            $zipcode->stripe_price_id,
            $zipcode->yearlyPriceCents(),
            $currency,
        );

        $monthlyPriceId = $this->syncIntervalPrice(
            $zipcode,
            $productId,
            Zipcode::BILLING_MONTHLY,
            $zipcode->stripe_monthly_price_id,
            $zipcode->monthlyPriceCents(),
            $currency,
        );

        $zipcode->update([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $yearlyPriceId,
            'stripe_monthly_price_id' => $monthlyPriceId,
        ]);

        return [
            'product_id' => $productId,
            'yearly_price_id' => $yearlyPriceId,
            'monthly_price_id' => $monthlyPriceId,
        ];
    }

    protected function syncIntervalPrice(
        Zipcode $zipcode,
        string $productId,
        string $interval,
        ?string $existingPriceId,
        int $amountCents,
        string $currency,
    ): ?string {
        if ($amountCents <= 0) {
            return null;
        }

        $client = $this->stripe->client();

        if ($existingPriceId) {
            try {
                $existing = $client->prices->retrieve($existingPriceId);

                if (
                    (int) $existing->unit_amount === $amountCents
                    && $existing->currency === $currency
                    && ($existing->recurring?->interval ?? null) === $interval
                    && $existing->product === $productId
                ) {
                    return $existingPriceId;
                }
            } catch (\Throwable) {
                //
            }
        }

        $price = $client->prices->create([
            'product' => $productId,
            'currency' => $currency,
            'unit_amount' => $amountCents,
            'recurring' => [
                'interval' => $interval,
            ],
            'metadata' => [
                'zipcode_id' => (string) $zipcode->id,
                'zipcode_code' => $zipcode->code,
                'billing_interval' => $interval,
            ],
        ]);

        return $price->id;
    }

    protected function productName(Zipcode $zipcode): string
    {
        return "VIU Territory ZIP {$zipcode->code}";
    }

    protected function productDescription(Zipcode $zipcode): string
    {
        return trim(collect([$zipcode->city, $zipcode->state])->filter()->implode(', '));
    }
}
