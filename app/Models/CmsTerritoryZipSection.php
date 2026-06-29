<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsTerritoryZipSection extends Model
{
    protected $fillable = [
        'badge_text',
        'headline_primary',
        'headline_accent',
        'intro',
        'checklist_items',
        'feature_columns',
        'left_visual_image_path',
        'left_card_icon_path',
        'card_kicker',
        'card_title',
        'checklist_check_icon_path',
        'quote_icon_path',
        'quote_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checklist_items' => 'array',
            'feature_columns' => 'array',
        ];
    }

    protected $appends = [
        'left_visual_image_url',
        'left_card_icon_url',
        'quote_icon_url',
        'checklist_check_icon_url',
    ];

    public static function singleton(): self
    {
        $defaults = [
            'badge_text' => 'Territory lock engaged',
            'headline_primary' => 'One ZIP.',
            'headline_accent' => 'One agent.',
            'intro' => 'Every ZIP inside Viu is assigned to a single agent at a time. While active, no other agent can enter that ZIP.',
            'card_kicker' => 'ZIP TERRITORY: 90210',
            'card_title' => 'MARKET OWNERSHIP',
            'left_visual_image_path' => null,
            'left_card_icon_path' => null,
            'checklist_check_icon_path' => null,
            'quote_icon_path' => null,
            'quote_text' => '“When a ZIP is live, it’s yours. Period.”',
            'checklist_items' => [
                ['text' => 'Predictive Homeowner Targeting Active'],
                ['text' => 'Daily Brand Reinforcement Running'],
                ['text' => 'Competitor Lockout Engaged'],
                ['text' => 'Market Share Compounding'],
            ],
            'feature_columns' => [
                ['icon_path' => 'image/territory-ico1.png', 'label' => 'NO SHARING'],
                ['icon_path' => 'image/territory-ico2.png', 'label' => 'NO OVERLAP'],
                ['icon_path' => 'image/territory-ico3.png', 'label' => 'NO CONGESTION'],
            ],
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

    public function getLeftVisualImageUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->left_visual_image_path);

        return $url ?? asset('viu/assets/images/section-2.jpg');
    }

    public function getLeftCardIconUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->left_card_icon_path);

        return $url ?? asset('image/ZIP-Territory-ico.png');
    }

    public function getQuoteIconUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->quote_icon_path);

        return $url ?? asset('image/territory-ico4.png');
    }

    public function getChecklistCheckIconUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->checklist_check_icon_path);

        return $url ?? asset('image/check-box.png');
    }

    /**
     * @return list<non-falsy-string>
     */
    public function checklistLines(): array
    {
        $raw = $this->checklist_items ?? [];
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

    /**
     * @return list<array{icon_path?: string|null, label?: string}>
     */
    public function featureList(): array
    {
        $list = $this->feature_columns ?? [];

        return is_array($list) ? $list : [];
    }

    /**
     * @param  array{icon_path?: string|null, label?: string}  $feature
     */
    public function featureIconUrl(array $feature, int $index): string
    {
        $bundled = [
            'image/territory-ico1.png',
            'image/territory-ico2.png',
            'image/territory-ico3.png',
        ];
        $url = static::publicUrlFor($feature['icon_path'] ?? null);
        if ($url !== null) {
            return $url;
        }

        return asset($bundled[$index] ?? $bundled[0]);
    }
}
