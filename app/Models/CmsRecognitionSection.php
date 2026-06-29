<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsRecognitionSection extends Model
{
    protected $fillable = [
        'badge_text',
        'headline_line_1',
        'headline_line_2',
        'headline_line_3',
        'intro',
        'box_top_left',
        'box_top_right',
        'box_wide_body',
        'box_wide_accent',
        'right_image_path',
        'card_logo_path',
        'card_kicker',
        'card_title',
        'card_progress_label_left',
        'card_progress_label_right',
        'card_progress_percent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'card_progress_percent' => 'integer',
        ];
    }

    protected $appends = [
        'right_image_url',
        'card_logo_url',
    ];

    public static function singleton(): self
    {
        $defaults = [
            'badge_text' => 'Permanent brand authority',
            'headline_line_1' => 'Built for',
            'headline_line_2' => 'Long-term',
            'headline_line_3' => 'Recognition',
            'intro' => 'Viu places your brand across the sites homeowners already visit, creating consistent visibility over time.',
            'box_top_left' => 'Some Homeowners Engage Quickly.',
            'box_top_right' => 'Others Take Longer.',
            'box_wide_body' => 'What Matters Is That When The Moment Arrives, Your Name Isn’t New — It’s Already Known.',
            'box_wide_accent' => 'Familiarity creates trust',
            'right_image_path' => null,
            'card_logo_path' => null,
            'card_kicker' => 'Market authority',
            'card_title' => 'Continuous visibility',
            'card_progress_label_left' => 'Brand domination',
            'card_progress_label_right' => 'Compounding',
            'card_progress_percent' => 95,
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

    public function getRightImageUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->right_image_path);

        return $url ?? asset('viu/assets/images/section-3.jpg');
    }

    public function getCardLogoUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->card_logo_path);

        return $url ?? asset('viu/assets/images/logo-dark.svg');
    }

    public function progressPercentClamped(): int
    {
        return min(100, max(0, (int) $this->card_progress_percent));
    }
}
