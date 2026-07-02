<?php

namespace App\Cms\Services;

use App\Cms\Enums\PageBlockType;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Support\Arr;

class PageSectionSync
{
    /**
     * @param  list<array{type?: string, data?: array<string, mixed>}>|null  $blocks
     */
    public function sync(Page $page, ?array $blocks): void
    {
        $page->sections()->delete();

        if (! is_array($blocks)) {
            return;
        }

        foreach (array_values($blocks) as $index => $block) {
            $type = Arr::get($block, 'type');
            $data = Arr::get($block, 'data', []);

            if (! is_string($type) || $type === '') {
                continue;
            }

            if (! PageBlockType::tryFrom($type)) {
                continue;
            }

            PageSection::query()->create([
                'page_id' => $page->id,
                'type' => $type,
                'content' => is_array($data) ? $data : [],
                'sort_order' => $index,
                'is_visible' => true,
            ]);
        }
    }

    /**
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    public function toBuilderState(Page $page): array
    {
        return $page->sections()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PageSection $section) => [
                'type' => $section->type->value,
                'data' => $section->content ?? [],
            ])
            ->values()
            ->all();
    }
}
