<?php

namespace App\Support;

use App\Cms\Support\MediaUrlResolver;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    private const CACHE_KEY = 'site_settings';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function model(): SiteSetting
    {
        if (! SiteSetting::tableExists()) {
            return new SiteSetting(SiteSetting::defaultsFromEnv());
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => SiteSetting::singleton());
    }

    public static function siteName(): string
    {
        $name = self::model()->site_name;

        return filled($name) ? $name : config('app.name', 'VIU');
    }

    public static function siteTagline(): ?string
    {
        return filled(self::model()->site_tagline)
            ? self::model()->site_tagline
            : null;
    }

    public static function logoLightUrl(): string
    {
        return MediaUrlResolver::image(
            self::model()->logo_light_path,
            'viu/assets/images/logo-white.svg'
        ) ?? asset('viu/assets/images/logo-white.svg');
    }

    public static function logoDarkUrl(): string
    {
        return MediaUrlResolver::image(
            self::model()->logo_dark_path,
            'viu/assets/images/logo-dark.svg'
        ) ?? asset('viu/assets/images/logo-dark.svg');
    }

    public static function faviconUrl(): string
    {
        return MediaUrlResolver::image(
            self::model()->favicon_path,
            'viu/assets/images/logo-dark.svg'
        ) ?? asset('viu/assets/images/logo-dark.svg');
    }

    public static function adminPanelLogoUrl(): string
    {
        return MediaUrlResolver::image(
            self::model()->admin_panel_logo_path,
            'image/logo-viu.png'
        ) ?? asset('image/logo-viu.png');
    }

    public static function footerLogoUrl(): string
    {
        return MediaUrlResolver::image(
            self::model()->footer_logo_path,
            'viu/assets/images/logo-white.svg'
        ) ?? asset('viu/assets/images/logo-white.svg');
    }

    public static function contactEmail(): ?string
    {
        return filled(self::model()->contact_email)
            ? self::model()->contact_email
            : null;
    }

    public static function supportEmail(): ?string
    {
        return filled(self::model()->support_email)
            ? self::model()->support_email
            : null;
    }

    public static function phoneNumber(): ?string
    {
        return filled(self::model()->phone_number)
            ? self::model()->phone_number
            : null;
    }

    public static function whatsappNumber(): ?string
    {
        return filled(self::model()->whatsapp_number)
            ? self::model()->whatsapp_number
            : null;
    }

    public static function whatsappUrl(): ?string
    {
        $number = self::whatsappNumber();

        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        return $digits !== '' ? 'https://wa.me/'.$digits : null;
    }

    public static function address(): ?string
    {
        return filled(self::model()->address)
            ? self::model()->address
            : null;
    }

    public static function googleMapEmbedUrl(): ?string
    {
        return filled(self::model()->google_map_embed_url)
            ? self::model()->google_map_embed_url
            : null;
    }
}
