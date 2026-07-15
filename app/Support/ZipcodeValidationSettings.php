<?php

namespace App\Support;

use App\Models\Setting;

class ZipcodeValidationSettings
{
    public const KEY_API_BASE_URL = 'zipcode_validation.api_base_url';

    public const DEFAULT_API_BASE_URL = 'https://api.zippopotam.us/us/';

    public static function apiBaseUrl(): string
    {
        $value = Setting::get(self::KEY_API_BASE_URL);

        return filled($value) ? rtrim((string) $value, '/').'/' : self::DEFAULT_API_BASE_URL;
    }

    public static function setApiBaseUrl(?string $url): void
    {
        Setting::set(self::KEY_API_BASE_URL, filled($url) ? rtrim($url, '/').'/' : '');
    }
}
