<?php

namespace App\Support;

use App\Models\CmsHeroSection;
use App\Models\CmsPricingSection;
use App\Models\CmsQaSection;
use App\Models\CmsRecognitionSection;
use App\Models\CmsStrategicWindowSection;
use App\Models\CmsTerritoryZipSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class CmsPublicApiPayload
{
    public const CACHE_KEY = 'cms.api.payload.v1';

    public static function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{version: string, sections: array<string, mixed>}
     */
    public static function cached(): array
    {
        $ttl = (int) config('cms.api_cache_ttl', 3600);

        return Cache::remember(self::CACHE_KEY, max(1, $ttl), fn (): array => self::build());
    }

    /**
     * Build payload without touching model events (avoids clearing cache mid-build).
     *
     * @return array{version: string, sections: array<string, mixed>}
     */
    public static function build(): array
    {
        return Model::withoutEvents(function (): array {
            $hero = CmsHeroSection::singleton();
            $strategicWindow = CmsStrategicWindowSection::singleton();
            $territoryZip = CmsTerritoryZipSection::singleton();
            $recognition = CmsRecognitionSection::singleton();
            $pricing = CmsPricingSection::singleton();
            $qa = CmsQaSection::singleton();

            $version = self::versionFromModels(
                $hero,
                $strategicWindow,
                $territoryZip,
                $recognition,
                $pricing,
                $qa,
            );

            return [
                'version' => $version,
                'sections' => [
                    'hero' => $hero->toArray(),
                    'strategic_window' => self::strategicWindowArray($strategicWindow),
                    'territory_zip' => self::territoryZipArray($territoryZip),
                    'recognition' => $recognition->toArray(),
                    'pricing' => $pricing->toArray(),
                    'qa' => $qa->toArray(),
                ],
            ];
        });
    }

    /**
     * @param  list<Model>  $models
     */
    private static function versionFromModels(Model ...$models): string
    {
        $parts = [];
        foreach ($models as $model) {
            $ts = $model->getAttribute('updated_at');
            $parts[] = $ts !== null ? (string) $ts : '';
        }

        return hash('xxh128', implode("\0", $parts));
    }

    /**
     * @return array<string, mixed>
     */
    private static function strategicWindowArray(CmsStrategicWindowSection $section): array
    {
        $base = $section->toArray();
        $base['features'] = collect($section->featureList())
            ->values()
            ->map(fn (array $row, int $index): array => array_merge($row, [
                'icon_url' => $section->featureIconUrl($row, $index),
            ]))
            ->all();

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private static function territoryZipArray(CmsTerritoryZipSection $section): array
    {
        $base = $section->toArray();
        $base['feature_columns'] = collect($section->featureList())
            ->values()
            ->map(fn (array $row, int $index): array => array_merge($row, [
                'icon_url' => $section->featureIconUrl($row, $index),
            ]))
            ->all();

        return $base;
    }
}
