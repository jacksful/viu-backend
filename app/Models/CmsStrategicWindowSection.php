<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsStrategicWindowSection extends Model
{
    protected $fillable = [
        'badge_text',
        'headline_primary',
        'headline_accent',
        'intro',
        'features',
        'visual_image_path',
        'card_icon_path',
        'card_kicker',
        'card_title',
        'card_metric_label',
        'card_metric_percent',
        'card_quote',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'card_metric_percent' => 'integer',
        ];
    }

    protected $appends = [
        'visual_image_url',
        'card_icon_url',
    ];

    /**
     * Single site-wide “90-day strategic window” / solutions block.
     */
    public static function singleton(): self
    {
        $defaults = [
            'badge_text' => 'The 90-day strategic window',
            'headline_primary' => 'Be first.',
            'headline_accent' => 'Be known.',
            'intro' => 'Homeowners don’t decide to sell overnight. There’s a window often 90 days or more where they’re observing the market before any formal action.',
            'features' => [
                [
                    'icon_path' => 'image/Container.png',
                    'title' => 'Identification',
                    'description' => 'Viu identifies homeowners likely to sell during that critical window using predictive behavior analytics.',
                ],
                [
                    'icon_path' => 'image/Container1.png',
                    'title' => 'Consistent presence',
                    'description' => 'Your brand appears daily on the platforms they trust, building familiarity long before search begins.',
                ],
                [
                    'icon_path' => 'image/Container2.png',
                    'title' => 'Market advantage',
                    'description' => 'When they finally decide to reach out, your name isn’t new—it’s already the market authority.',
                ],
            ],
            'visual_image_path' => null,
            'card_icon_path' => null,
            'card_kicker' => 'Predictive signal',
            'card_title' => '90210 market intensity',
            'card_metric_label' => 'Early interest',
            'card_metric_percent' => 42,
            'card_quote' => 'Capturing attention 3 months before listing behavior peaks.',
        ];

        return static::query()->firstOrCreate([], $defaults);
    }

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

    public function getVisualImageUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->visual_image_path);

        return $url ?? asset('image/be-first.png');
    }

    public function getCardIconUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->card_icon_path);

        return $url ?? asset('image/productive-signal.png');
    }

    /**
     * @param  array{icon_path?: string|null}  $feature
     */
    public function featureIconUrl(array $feature, int $index): string
    {
        $bundled = ['image/Container.png', 'image/Container1.png', 'image/Container2.png'];
        $url = static::publicUrlFor($feature['icon_path'] ?? null);
        if ($url !== null) {
            return $url;
        }

        return asset($bundled[$index] ?? $bundled[0]);
    }

    /**
     * @return list<array{icon_path?: string|null, title?: string, description?: string}>
     */
    public function featureList(): array
    {
        $list = $this->features ?? [];

        return is_array($list) ? $list : [];
    }
}
