<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAboutPrinciplesSection extends Model
{
    protected $fillable = [
        'badge_text',
        'heading',
        'principles',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'principles' => 'array',
        ];
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate([], [
            'badge_text' => 'What sets us apart',
            'heading' => 'Three principles behind the platform',
            'principles' => [
                [
                    'title' => 'One agent per ZIP',
                    'description' => "Every ZIP is reserved for a single active subscriber. While it's yours, no other agent can enter: no sharing, no overlap, no congestion.",
                ],
                [
                    'title' => 'Predictive timing',
                    'description' => 'We model the signals that precede a sale, placing your brand in front of likely sellers up to 90 days before they decide to list.',
                ],
                [
                    'title' => 'Permanent authority',
                    'description' => "Consistent presence across the sites homeowners already trust builds familiarity that compounds, so your name is known before it's needed.",
                ],
            ],
        ]);
    }

    /**
     * @return list<array{title?: string, description?: string}>
     */
    public function principleList(): array
    {
        $list = $this->principles ?? [];

        return is_array($list) ? $list : [];
    }
}
