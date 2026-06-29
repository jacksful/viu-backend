<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAboutMissionSection extends Model
{
    protected $fillable = [
        'badge_text',
        'headline',
        'intro_text',
        'body_middle',
        'body_last',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate([], [
            'badge_text' => 'Our mission',
            'headline' => 'Visibility is earned early, not bought late',
            'intro_text' => "Most marketing reaches homeowners once they're already searching, when every agent is competing for the same attention. By then, the window has closed.",
            'body_middle' => 'VIU works the other way around. Our predictive model surfaces intent before search patterns emerge, so your brand is already familiar when the moment arrives. One agent per ZIP, compounding recognition over time, and a market that knows your name before they need it.',
            'body_last' => "It's a quieter, more durable kind of marketing, built for professionals who plan to own their market for years, not weeks.",
        ]);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return asset('viu/assets/images/section-1.jpg');
    }
}
