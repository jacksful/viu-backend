<?php

namespace App\Cms\Support;

use App\Cms\Enums\PageRobots;
use App\Models\Page;
use App\Support\TrackingSocialSettings;

class PageSeo
{
    public function __construct(
        private Page $page,
        private bool $preview = false,
    ) {}

    public static function for(Page $page, bool $preview = false): self
    {
        return new self($page, $preview);
    }

    public function metaTitle(): string
    {
        return filled($this->page->seo_title)
            ? $this->page->seo_title
            : $this->page->title;
    }

    public function metaDescription(): ?string
    {
        if (filled($this->page->seo_description)) {
            return $this->page->seo_description;
        }

        return TrackingSocialSettings::defaultMetaDescription();
    }

    public function metaKeywords(): ?string
    {
        if (filled($this->page->meta_keywords)) {
            return $this->page->meta_keywords;
        }

        return TrackingSocialSettings::defaultMetaKeywords();
    }

    public function robots(): string
    {
        if ($this->preview) {
            return PageRobots::NoindexNofollow->value;
        }

        $robots = $this->page->robots;

        if ($robots instanceof PageRobots) {
            return $robots->value;
        }

        if (is_string($robots) && $robots !== '') {
            return $robots;
        }

        return TrackingSocialSettings::defaultRobots();
    }

    public function canonicalUrl(): string
    {
        if (filled($this->page->canonical_url)) {
            return $this->page->canonical_url;
        }

        if ($this->page->is_homepage) {
            return url('/');
        }

        return $this->page->publicUrl();
    }

    public function ogTitle(): string
    {
        return filled($this->page->og_title) ? $this->page->og_title : $this->metaTitle();
    }

    public function ogDescription(): ?string
    {
        if (filled($this->page->og_description)) {
            return $this->page->og_description;
        }

        return $this->metaDescription();
    }

    public function ogImageUrl(): ?string
    {
        $url = MediaUrlResolver::image($this->page->og_image_path);

        return $url ?? TrackingSocialSettings::defaultOgImageUrl();
    }

    public function twitterTitle(): string
    {
        return filled($this->page->twitter_title) ? $this->page->twitter_title : $this->ogTitle();
    }

    public function twitterDescription(): ?string
    {
        if (filled($this->page->twitter_description)) {
            return $this->page->twitter_description;
        }

        return $this->ogDescription();
    }

    public function twitterImageUrl(): ?string
    {
        $url = MediaUrlResolver::image($this->page->twitter_image_path);

        return $url ?? $this->ogImageUrl();
    }

    /**
     * @return list<array{type: string, key: string, value: string}>
     */
    public function customMetaTags(): array
    {
        $tags = $this->page->meta_tags ?? [];

        if (! is_array($tags)) {
            return [];
        }

        $normalized = [];

        foreach ($tags as $tag) {
            if (! is_array($tag)) {
                continue;
            }

            $type = ($tag['type'] ?? 'name') === 'property' ? 'property' : 'name';
            $key = trim((string) ($tag['key'] ?? ''));
            $value = trim((string) ($tag['value'] ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $normalized[] = [
                'type' => $type,
                'key' => $key,
                'value' => $value,
            ];
        }

        return $normalized;
    }
}
