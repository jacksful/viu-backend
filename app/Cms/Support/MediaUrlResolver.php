<?php

namespace App\Cms\Support;

class MediaUrlResolver
{
    public static function publicUrlFor(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'cms/')) {
            return asset('storage/'.$path);
        }

        return asset($path);
    }

    public static function image(?string $path, ?string $fallback = null): ?string
    {
        $url = static::publicUrlFor($path);

        if ($url !== null) {
            return $url;
        }

        return $fallback !== null ? asset($fallback) : null;
    }
}
