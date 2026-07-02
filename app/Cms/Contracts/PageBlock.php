<?php

namespace App\Cms\Contracts;

use App\Cms\Enums\PageBlockType;
use App\Cms\Support\PageRenderContext;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Component;

interface PageBlock
{
    public static function type(): PageBlockType;

    public static function label(): string;

    public static function icon(): string;

    /**
     * @return array<int, Component|\Filament\Schemas\Components\Component>
     */
    public static function schema(): array;

    public static function filamentBlock(): Block;

    /**
     * @param  array<string, mixed>  $content
     */
    public static function presenter(array $content): object;

    public static function view(): string;

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function renderProps(array $content, PageRenderContext $context): array;
}
