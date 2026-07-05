<?php

namespace App\Support;

use App\Cms\Support\MediaUrlResolver;
use App\Models\TrackingSocialSetting;
use Illuminate\Support\Facades\Cache;

class TrackingSocialSettings
{
    private const CACHE_KEY = 'tracking_social_settings';

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function model(): TrackingSocialSetting
    {
        if (! TrackingSocialSetting::tableExists()) {
            return new TrackingSocialSetting(TrackingSocialSetting::defaultsFromEnv());
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => TrackingSocialSetting::singleton());
    }

    public static function googleAnalyticsEnabled(): bool
    {
        $settings = self::model();

        return $settings->google_analytics_enabled
            && filled($settings->google_analytics_measurement_id);
    }

    public static function googleAnalyticsMeasurementId(): ?string
    {
        return self::model()->google_analytics_measurement_id;
    }

    public static function googleTagManagerEnabled(): bool
    {
        $settings = self::model();

        return $settings->google_tag_manager_enabled
            && filled($settings->google_tag_manager_id);
    }

    public static function googleTagManagerId(): ?string
    {
        return self::model()->google_tag_manager_id;
    }

    public static function facebookPixelEnabled(): bool
    {
        $settings = self::model();

        return $settings->facebook_pixel_enabled
            && filled($settings->facebook_pixel_id);
    }

    public static function facebookPixelId(): ?string
    {
        return self::model()->facebook_pixel_id;
    }

    public static function tiktokPixelEnabled(): bool
    {
        $settings = self::model();

        return $settings->tiktok_pixel_enabled
            && filled($settings->tiktok_pixel_id);
    }

    public static function tiktokPixelId(): ?string
    {
        return self::model()->tiktok_pixel_id;
    }

    public static function linkedinInsightEnabled(): bool
    {
        $settings = self::model();

        return $settings->linkedin_insight_enabled
            && filled($settings->linkedin_insight_tag_id);
    }

    public static function linkedinInsightTagId(): ?string
    {
        return self::model()->linkedin_insight_tag_id;
    }

    public static function pinterestTagEnabled(): bool
    {
        $settings = self::model();

        return $settings->pinterest_tag_enabled
            && filled($settings->pinterest_tag_id);
    }

    public static function pinterestTagId(): ?string
    {
        return self::model()->pinterest_tag_id;
    }

    public static function twitterPixelEnabled(): bool
    {
        $settings = self::model();

        return $settings->twitter_pixel_enabled
            && filled($settings->twitter_pixel_id);
    }

    public static function twitterPixelId(): ?string
    {
        return self::model()->twitter_pixel_id;
    }

    public static function snapchatPixelEnabled(): bool
    {
        $settings = self::model();

        return $settings->snapchat_pixel_enabled
            && filled($settings->snapchat_pixel_id);
    }

    public static function snapchatPixelId(): ?string
    {
        return self::model()->snapchat_pixel_id;
    }

    public static function googleSearchConsoleVerification(): ?string
    {
        return self::model()->google_search_console_verification;
    }

    public static function facebookDomainVerification(): ?string
    {
        return self::model()->facebook_domain_verification;
    }

    public static function defaultMetaTitle(): ?string
    {
        return filled(self::model()->default_meta_title)
            ? self::model()->default_meta_title
            : null;
    }

    public static function defaultMetaDescription(): ?string
    {
        return filled(self::model()->default_meta_description)
            ? self::model()->default_meta_description
            : null;
    }

    public static function defaultMetaKeywords(): ?string
    {
        return filled(self::model()->default_meta_keywords)
            ? self::model()->default_meta_keywords
            : null;
    }

    public static function defaultRobots(): string
    {
        $robots = self::model()->default_robots;

        return filled($robots) ? $robots : 'index,follow';
    }

    public static function defaultOgImageUrl(): ?string
    {
        return MediaUrlResolver::image(self::model()->default_og_image_path);
    }

    /**
     * @return list<array{platform: string, url: string, label: string}>
     */
    public static function socialProfileLinks(): array
    {
        $settings = self::model();
        $links = [];

        $map = [
            'facebook' => ['url' => $settings->facebook_url, 'label' => 'VIU on Facebook'],
            'instagram' => ['url' => $settings->instagram_url, 'label' => 'VIU on Instagram'],
            'linkedin' => ['url' => $settings->linkedin_url, 'label' => 'VIU on LinkedIn'],
            'twitter' => ['url' => $settings->twitter_url, 'label' => 'VIU on X'],
            'youtube' => ['url' => $settings->youtube_url, 'label' => 'VIU on YouTube'],
            'tiktok' => ['url' => $settings->tiktok_url, 'label' => 'VIU on TikTok'],
            'pinterest' => ['url' => $settings->pinterest_url, 'label' => 'VIU on Pinterest'],
        ];

        foreach ($map as $platform => $item) {
            if (filled($item['url'])) {
                $links[] = [
                    'platform' => $platform,
                    'url' => $item['url'],
                    'label' => $item['label'],
                ];
            }
        }

        if (filled($settings->whatsapp_number)) {
            $digits = preg_replace('/\D+/', '', $settings->whatsapp_number) ?? '';

            if ($digits !== '') {
                $links[] = [
                    'platform' => 'whatsapp',
                    'url' => 'https://wa.me/'.$digits,
                    'label' => 'VIU on WhatsApp',
                ];
            }
        }

        return $links;
    }
}
