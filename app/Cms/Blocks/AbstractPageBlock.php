<?php

namespace App\Cms\Blocks;

use App\Cms\Contracts\PageBlock;
use App\Cms\Enums\PageBlockType;
use App\Cms\Support\PageRenderContext;
use Filament\Forms\Components\Builder\Block;

abstract class AbstractPageBlock implements PageBlock
{
    abstract public static function type(): PageBlockType;

    public static function label(): string
    {
        return static::type()->label();
    }

    public static function filamentBlock(): Block
    {
        return Block::make(static::type()->value)
            ->label(static::label())
            ->icon(static::icon())
            ->schema(static::schema());
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    public static function renderProps(array $content, PageRenderContext $context): array
    {
        return [
            'section' => static::presenter($content),
        ];
    }
}
