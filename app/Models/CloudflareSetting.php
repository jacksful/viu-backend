<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CloudflareSetting extends Model
{
    protected $fillable = [
        'enabled',
        'site_key',
        'secret_key',
        'admin_login_enabled',
        'customer_login_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'secret_key' => 'encrypted',
        'admin_login_enabled' => 'boolean',
        'customer_login_enabled' => 'boolean',
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
            'enabled' => (bool) env('CLOUDFLARE_TURNSTILE_ENABLED', false),
            'site_key' => env('CLOUDFLARE_TURNSTILE_SITE_KEY'),
            'secret_key' => env('CLOUDFLARE_TURNSTILE_SECRET_KEY'),
            'admin_login_enabled' => (bool) env('CLOUDFLARE_TURNSTILE_ADMIN_LOGIN', true),
            'customer_login_enabled' => (bool) env('CLOUDFLARE_TURNSTILE_CUSTOMER_LOGIN', true),
        ];
    }

    public static function applyConfig(): void
    {
        if (! Schema::hasTable('cloudflare_settings')) {
            return;
        }

        $settings = static::singleton();

        Config::set('services.cloudflare.turnstile.enabled', $settings->enabled);
        Config::set('services.cloudflare.turnstile.site_key', $settings->site_key);
        Config::set('services.cloudflare.turnstile.secret_key', $settings->secret_key);
        Config::set('services.cloudflare.turnstile.admin_login_enabled', $settings->admin_login_enabled);
        Config::set('services.cloudflare.turnstile.customer_login_enabled', $settings->customer_login_enabled);
    }

    public function isConfigured(): bool
    {
        return $this->enabled
            && filled($this->site_key)
            && filled($this->secret_key);
    }

    public function isRequiredForAdminLogin(): bool
    {
        return $this->isConfigured() && $this->admin_login_enabled;
    }

    public function isRequiredForCustomerLogin(): bool
    {
        return $this->isConfigured() && $this->customer_login_enabled;
    }
}
