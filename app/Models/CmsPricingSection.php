<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPricingSection extends Model
{
    protected $fillable = [
        'left_image_path',
        'card_label_starting',
        'card_price_display',
        'card_price_period',
        'card_per_label',
        'card_footer_note',
        'badge_text',
        'heading',
        'intro',
        'feature_lines',
        'cta_label',
        'cta_href',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'feature_lines' => 'array',
        ];
    }

    protected $appends = [
        'left_image_url',
    ];

    public static function singleton(): self
    {
        $defaults = [
            'left_image_path' => null,
            'card_label_starting' => 'Starting',
            'card_price_display' => '$199',
            'card_price_period' => '/mo',
            'card_per_label' => 'Per ZIP code',
            'card_footer_note' => 'Locked-in pricing for the duration of your active status.',
            'badge_text' => 'Investment structure',
            'heading' => 'Pricing',
            'intro' => 'Secure your territory today. Your access remains exclusive for as long as you’re active.',
            'feature_lines' => [
                ['text' => 'Your access remains exclusive for as long as you’re active.'],
                ['text' => 'If you cancel, the ZIP becomes available again.'],
                ['text' => 'Onboarding begins immediately.'],
                ['text' => 'Your brand typically goes live within one week.'],
            ],
            'cta_label' => 'Check ZIP availability',
            'cta_href' => '#hero-zip',
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

    public function getLeftImageUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->left_image_path);

        return $url ?? asset('image/pricing-image.jpg');
    }

    /**
     * @return list<non-falsy-string>
     */
    public function featureLines(): array
    {
        $raw = $this->feature_lines ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $lines = [];
        foreach ($raw as $row) {
            if (is_array($row)) {
                $t = trim((string) ($row['text'] ?? ''));
                if ($t !== '') {
                    $lines[] = $t;
                }
            } elseif (is_string($row) && trim($row) !== '') {
                $lines[] = trim($row);
            }
        }

        return $lines;
    }
}
