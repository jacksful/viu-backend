<?php

namespace App\Models;

use App\Support\TrackingSocialSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TrackingSocialSetting extends Model
{
    protected $fillable = [
        'google_analytics_measurement_id',
        'google_tag_manager_id',
        'google_search_console_verification',
        'google_analytics_enabled',
        'google_tag_manager_enabled',
        'facebook_pixel_id',
        'facebook_domain_verification',
        'facebook_pixel_enabled',
        'tiktok_pixel_id',
        'linkedin_insight_tag_id',
        'pinterest_tag_id',
        'twitter_pixel_id',
        'snapchat_pixel_id',
        'tiktok_pixel_enabled',
        'linkedin_insight_enabled',
        'pinterest_tag_enabled',
        'twitter_pixel_enabled',
        'snapchat_pixel_enabled',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'twitter_url',
        'youtube_url',
        'tiktok_url',
        'pinterest_url',
        'whatsapp_number',
        'default_meta_title',
        'default_meta_description',
        'default_meta_keywords',
        'default_og_image_path',
        'default_robots',
    ];

    protected $casts = [
        'google_analytics_enabled' => 'boolean',
        'google_tag_manager_enabled' => 'boolean',
        'facebook_pixel_enabled' => 'boolean',
        'tiktok_pixel_enabled' => 'boolean',
        'linkedin_insight_enabled' => 'boolean',
        'pinterest_tag_enabled' => 'boolean',
        'twitter_pixel_enabled' => 'boolean',
        'snapchat_pixel_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => TrackingSocialSettings::clearCache());
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
            'google_analytics_measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
            'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
            'google_search_console_verification' => env('GOOGLE_SEARCH_CONSOLE_VERIFICATION'),
            'google_analytics_enabled' => filter_var(env('GOOGLE_ANALYTICS_ENABLED', false), FILTER_VALIDATE_BOOL),
            'google_tag_manager_enabled' => filter_var(env('GOOGLE_TAG_MANAGER_ENABLED', false), FILTER_VALIDATE_BOOL),
            'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
            'facebook_domain_verification' => env('FACEBOOK_DOMAIN_VERIFICATION'),
            'facebook_pixel_enabled' => filter_var(env('FACEBOOK_PIXEL_ENABLED', false), FILTER_VALIDATE_BOOL),
            'tiktok_pixel_id' => env('TIKTOK_PIXEL_ID'),
            'linkedin_insight_tag_id' => env('LINKEDIN_INSIGHT_TAG_ID'),
            'pinterest_tag_id' => env('PINTEREST_TAG_ID'),
            'twitter_pixel_id' => env('TWITTER_PIXEL_ID'),
            'snapchat_pixel_id' => env('SNAPCHAT_PIXEL_ID'),
            'tiktok_pixel_enabled' => filter_var(env('TIKTOK_PIXEL_ENABLED', false), FILTER_VALIDATE_BOOL),
            'linkedin_insight_enabled' => filter_var(env('LINKEDIN_INSIGHT_ENABLED', false), FILTER_VALIDATE_BOOL),
            'pinterest_tag_enabled' => filter_var(env('PINTEREST_TAG_ENABLED', false), FILTER_VALIDATE_BOOL),
            'twitter_pixel_enabled' => filter_var(env('TWITTER_PIXEL_ENABLED', false), FILTER_VALIDATE_BOOL),
            'snapchat_pixel_enabled' => filter_var(env('SNAPCHAT_PIXEL_ENABLED', false), FILTER_VALIDATE_BOOL),
            'facebook_url' => env('SOCIAL_FACEBOOK_URL'),
            'instagram_url' => env('SOCIAL_INSTAGRAM_URL'),
            'linkedin_url' => env('SOCIAL_LINKEDIN_URL'),
            'twitter_url' => env('SOCIAL_TWITTER_URL'),
            'youtube_url' => env('SOCIAL_YOUTUBE_URL'),
            'tiktok_url' => env('SOCIAL_TIKTOK_URL'),
            'pinterest_url' => env('SOCIAL_PINTEREST_URL'),
            'whatsapp_number' => env('SOCIAL_WHATSAPP_NUMBER'),
            'default_meta_title' => env('DEFAULT_META_TITLE'),
            'default_meta_description' => env('DEFAULT_META_DESCRIPTION'),
            'default_meta_keywords' => env('DEFAULT_META_KEYWORDS'),
            'default_og_image_path' => env('DEFAULT_OG_IMAGE_PATH'),
            'default_robots' => env('DEFAULT_ROBOTS', 'index,follow'),
        ];
    }

    public static function tableExists(): bool
    {
        return Schema::hasTable('tracking_social_settings');
    }
}
