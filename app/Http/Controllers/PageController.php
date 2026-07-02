<?php

namespace App\Http\Controllers;

use App\Cms\Services\PageRenderer;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(PageRenderer $renderer): View
    {
        $page = Page::query()
            ->published()
            ->where('is_homepage', true)
            ->firstOrFail();

        return $this->render($page, $renderer);
    }

    public function show(string $slug, PageRenderer $renderer): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->render($page, $renderer);
    }

    public function preview(Request $request, Page $page, PageRenderer $renderer): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return $this->render($page, $renderer, preview: true);
    }

    protected function render(Page $page, PageRenderer $renderer, bool $preview = false): View
    {
        $page->load('sections');

        return view('pages.show', [
            'page' => $page,
            'sections' => $renderer->render($page),
            'preview' => $preview,
        ]);
    }
}
