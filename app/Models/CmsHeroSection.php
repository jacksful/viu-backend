<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsHeroSection extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Single site-wide hero row. Creates defaults on first use (admin or home).
     */
    public static function singleton(): self
    {
        $defaults = [
            'title' => "Own the market\nbefore they sell",
            'description' => 'Viu uses predictive modeling to place your brand in front of homeowners up to 90 days before they decide to sell securing your position before search even begins.',
        ];

        return static::query()->firstOrCreate([], $defaults);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/'.$this->image_path);
    }
}
