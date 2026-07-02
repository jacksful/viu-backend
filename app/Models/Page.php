<?php

namespace App\Models;

use App\Cms\Enums\PageMenuPosition;
use App\Cms\Enums\PageRobots;
use App\Cms\Enums\PageStatus;
use App\Cms\Support\PageSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'seo_title',
        'seo_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_path',
        'twitter_title',
        'twitter_description',
        'twitter_image_path',
        'robots',
        'meta_tags',
        'status',
        'sort_order',
        'is_homepage',
        'menu_label',
        'menu_sort_order',
        'menu_positions',
        'body_class',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'robots' => PageRobots::class,
            'meta_tags' => 'array',
            'menu_positions' => 'array',
            'is_homepage' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }

    public function seo(bool $preview = false): PageSeo
    {
        return PageSeo::for($this, $preview);
    }

    public function menuLabel(): string
    {
        return filled($this->menu_label) ? $this->menu_label : $this->title;
    }

    public function hasMenuPosition(PageMenuPosition|string $position): bool
    {
        $value = $position instanceof PageMenuPosition ? $position->value : $position;
        $positions = $this->menu_positions ?? [];

        return is_array($positions) && in_array($value, $positions, true);
    }

    public function publicUrl(): string
    {
        if ($this->is_homepage) {
            return url('/');
        }

        return match ($this->slug) {
            'about' => route('about'),
            'privacy' => route('privacy'),
            'terms' => route('terms'),
            default => route('pages.show', ['slug' => $this->slug]),
        };
    }

    public function resolvedBodyClass(): string
    {
        if (filled($this->body_class)) {
            return $this->body_class;
        }

        if ($this->is_homepage) {
            return 'home-page';
        }

        return 'cms-page cms-page--'.$this->slug;
    }

    public function scopePublished($query)
    {
        return $query->where('status', PageStatus::Published);
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if ($page->status === PageStatus::Published && $page->published_at === null) {
                $page->published_at = now();
            }

            if ($page->is_homepage) {
                static::query()
                    ->where('id', '!=', $page->id ?? 0)
                    ->where('is_homepage', true)
                    ->update(['is_homepage' => false]);
            }
        });
    }
}
