<?php

namespace App\Models;

use App\Support\SiteSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo_light_path',
        'logo_dark_path',
        'favicon_path',
        'admin_panel_logo_path',
        'footer_logo_path',
        'contact_email',
        'support_email',
        'phone_number',
        'whatsapp_number',
        'address',
        'google_map_embed_url',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => SiteSettings::clearCache());
    }

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
            'site_name' => env('SITE_NAME', config('app.name', 'VIU')),
            'site_tagline' => env('SITE_TAGLINE', 'Own the market before they sell'),
            'logo_light_path' => env('SITE_LOGO_LIGHT_PATH', 'viu/assets/images/logo-white.svg'),
            'logo_dark_path' => env('SITE_LOGO_DARK_PATH', 'viu/assets/images/logo-dark.svg'),
            'favicon_path' => env('SITE_FAVICON_PATH', 'viu/assets/images/logo-dark.svg'),
            'admin_panel_logo_path' => env('SITE_ADMIN_PANEL_LOGO_PATH', 'image/logo-viu.png'),
            'footer_logo_path' => env('SITE_FOOTER_LOGO_PATH', 'viu/assets/images/logo-white.svg'),
            'contact_email' => env('SITE_CONTACT_EMAIL'),
            'support_email' => env('SITE_SUPPORT_EMAIL', 'support@fullviu.com'),
            'phone_number' => env('SITE_PHONE_NUMBER'),
            'whatsapp_number' => env('SITE_WHATSAPP_NUMBER'),
            'address' => env('SITE_ADDRESS', 'Billings, Montana, USA'),
            'google_map_embed_url' => env('SITE_GOOGLE_MAP_EMBED_URL'),
        ];
    }

    public static function tableExists(): bool
    {
        return Schema::hasTable('site_settings');
    }
}
