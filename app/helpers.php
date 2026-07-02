<?php

use App\Models\SiteSetting;
use App\Models\TrackingSocialSetting;
use App\Support\SiteSettings;
use App\Support\TrackingSocialSettings;

if (! function_exists('site_settings')) {
    /**
     * Return the cached site-wide branding and contact settings model.
     */
    function site_settings(): SiteSetting
    {
        return SiteSettings::model();
    }
}

if (! function_exists('tracking_social')) {
    /**
     * Return the cached site-wide tracking, social, and SEO settings model.
     */
    function tracking_social(): TrackingSocialSetting
    {
        return TrackingSocialSettings::model();
    }
}

if (! function_exists('tracking_social_enabled')) {
    /**
     * Check whether a tracking integration is active and configured.
     *
     * @param  'google_analytics'|'google_tag_manager'|'facebook_pixel'|'tiktok_pixel'|'linkedin_insight'|'pinterest_tag'|'twitter_pixel'|'snapchat_pixel'  $platform
     */
    function tracking_social_enabled(string $platform): bool
    {
        return match ($platform) {
            'google_analytics' => TrackingSocialSettings::googleAnalyticsEnabled(),
            'google_tag_manager' => TrackingSocialSettings::googleTagManagerEnabled(),
            'facebook_pixel' => TrackingSocialSettings::facebookPixelEnabled(),
            'tiktok_pixel' => TrackingSocialSettings::tiktokPixelEnabled(),
            'linkedin_insight' => TrackingSocialSettings::linkedinInsightEnabled(),
            'pinterest_tag' => TrackingSocialSettings::pinterestTagEnabled(),
            'twitter_pixel' => TrackingSocialSettings::twitterPixelEnabled(),
            'snapchat_pixel' => TrackingSocialSettings::snapchatPixelEnabled(),
            default => false,
        };
    }
}
