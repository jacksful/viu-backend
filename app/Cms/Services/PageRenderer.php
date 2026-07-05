<?php

namespace App\Cms\Services;

use App\Cms\Support\BlockRegistry;
use App\Cms\Support\PageRenderContext;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PageRenderer
{
    public function __construct(
        private PageRenderContext $context,
    ) {}

    /**
     * @return Collection<int, array{view: string, data: array<string, mixed>}>
     */
    public function render(Page $page): Collection
    {
        return $page->sections
            ->where('is_visible', true)
            ->values()
            ->map(function ($section) {
                $blockClass = BlockRegistry::resolveClass($section->type);

                if ($blockClass === null) {
                    Log::warning('Unknown CMS page block type.', [
                        'type' => $section->type?->value ?? $section->type,
                        'section_id' => $section->id,
                    ]);

                    return null;
                }

                $content = is_array($section->content) ? $section->content : [];

                return [
                    'view' => $blockClass::view(),
                    'data' => $blockClass::renderProps($content, $this->context),
                ];
            })
            ->filter()
            ->values();
    }
}
