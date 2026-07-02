<?php

namespace App\Cms\Support;

use App\Cms\Enums\PageMenuPosition;
use App\Models\Page;
use Illuminate\Support\Collection;

class SiteNavigation
{
    /**
     * @return list<array{label: string, url: string}>
     */
    public static function sectionLinks(string $zone): array
    {
        $key = $zone === 'footer' ? 'footer_section_links' : 'header_section_links';

        return collect(config("cms.{$key}", []))
            ->map(fn (array $link) => [
                'label' => $link['label'],
                'url' => self::normalizeUrl($link['url']),
            ])
            ->all();
    }

    /**
     * @return Collection<int, array{label: string, url: string}>
     */
    public static function pageLinks(PageMenuPosition $position): Collection
    {
        if (! config('cms.use_page_builder')) {
            return collect();
        }

        return Page::query()
            ->published()
            ->whereJsonContains('menu_positions', $position->value)
            ->orderBy('menu_sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Page $page) => [
                'label' => $page->menuLabel(),
                'url' => $page->publicUrl(),
            ]);
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function headerLinks(): array
    {
        return array_merge(
            static::sectionLinks('header'),
            static::pageLinks(PageMenuPosition::Header)->all(),
        );
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function footerLinks(): array
    {
        return array_merge(
            static::sectionLinks('footer'),
            static::pageLinks(PageMenuPosition::Footer)->all(),
        );
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public static function copyrightLinks(): array
    {
        if (! config('cms.use_page_builder')) {
            return [
                ['label' => 'Privacy', 'url' => route('privacy')],
                ['label' => 'Terms', 'url' => route('terms')],
            ];
        }

        $links = static::pageLinks(PageMenuPosition::Copyright)->all();

        if ($links !== []) {
            return $links;
        }

        return [
            ['label' => 'Privacy', 'url' => route('privacy')],
            ['label' => 'Terms', 'url' => route('terms')],
        ];
    }

    protected static function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }
}
