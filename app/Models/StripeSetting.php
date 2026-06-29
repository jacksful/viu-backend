<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class StripeSetting extends Model
{
    protected $fillable = [
        'enabled',
        'test_mode',
        'publishable_key',
        'secret_key',
        'webhook_secret',
        'currency',
        'success_url',
        'cancel_url',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'test_mode' => 'boolean',
        'secret_key' => 'encrypted',
        'webhook_secret' => 'encrypted',
    ];

    public static function singleton(): self
    {
        $existing = static::query()->first();

        if ($existing) {
            return $existing;
        }

        return static::query()->create(static::defaultsFromEnv());
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultsFromEnv(): array
    {
        return [
            'enabled' => (bool) env('STRIPE_ENABLED', false),
            'test_mode' => (bool) env('STRIPE_TEST_MODE', true),
            'publishable_key' => env('STRIPE_KEY'),
            'secret_key' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'usd'),
            'success_url' => env('STRIPE_SUCCESS_URL'),
            'cancel_url' => env('STRIPE_CANCEL_URL'),
        ];
    }

    public static function applyConfig(): void
    {
        if (! Schema::hasTable('stripe_settings')) {
            return;
        }

        $settings = static::singleton();

        Config::set('services.stripe.enabled', $settings->enabled);
        Config::set('services.stripe.test_mode', $settings->test_mode);
        Config::set('services.stripe.key', $settings->publishable_key);
        Config::set('services.stripe.secret', $settings->secret_key);
        Config::set('services.stripe.webhook_secret', $settings->webhook_secret);
        Config::set('services.stripe.currency', $settings->currency ?: 'usd');
        Config::set('services.stripe.success_url', $settings->success_url);
        Config::set('services.stripe.cancel_url', $settings->cancel_url);
    }

    public function isConfigured(): bool
    {
        return $this->enabled
            && filled($this->publishable_key)
            && filled($this->secret_key);
    }

    public function resolvedSuccessUrl(): string
    {
        return $this->success_url ?: route('stripe.checkout.success');
    }

    public function resolvedCancelUrl(): string
    {
        return $this->cancel_url ?: url('/?checkout=cancelled');
    }
}
