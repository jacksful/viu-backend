<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAboutHeroSection extends Model
{
    protected $fillable = [
        'badge_text',
        'title',
        'lead',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate([], [
            'badge_text' => 'About VIU',
            'title' => "We put your brand in front of the market\nbefore it moves",
            'lead' => 'VIU is predictive brand positioning for real estate professionals. We identify the homeowners most likely to sell and keep you visible through the quiet months, long before anyone starts searching for an agent.',
        ]);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }
}
