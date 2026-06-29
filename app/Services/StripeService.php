<?php

namespace App\Services;

use App\Models\StripeSetting;
use Stripe\StripeClient;

class StripeService
{
    protected ?StripeClient $client = null;

    public function settings(): StripeSetting
    {
        return StripeSetting::singleton();
    }

    public function isEnabled(): bool
    {
        return $this->settings()->isConfigured();
    }

    public function client(): StripeClient
    {
        if ($this->client) {
            return $this->client;
        }

        $secret = config('services.stripe.secret') ?: $this->settings()->secret_key;

        if (! filled($secret)) {
            throw new \RuntimeException('Stripe secret key is not configured.');
        }

        $this->client = new StripeClient($secret);

        return $this->client;
    }

    public function publishableKey(): ?string
    {
        return config('services.stripe.key') ?: $this->settings()->publishable_key;
    }
}
