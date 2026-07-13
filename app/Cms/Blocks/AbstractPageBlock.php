<?php

namespace App\Cms\Blocks;

use App\Cms\Contracts\PageBlock;
use App\Cms\Enums\PageBlockType;
use App\Cms\Support\PageRenderContext;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\Flex;

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

    /**
     * @param  array<int, \Filament\Forms\Components\Component|\Filament\Schemas\Components\Component>  $leftSchema
     * @param  array<int, \Filament\Forms\Components\Component|\Filament\Schemas\Components\Component>  $rightSchema
     * @return array<int, Flex>
     */
    protected static function sideBySideColumns(
        string $leftLabel,
        array $leftSchema,
        string $rightLabel,
        array $rightSchema,
        int $innerColumns = 2,
    ): array {
        return [
            Flex::make([
                SchemaComponents\Section::make($leftLabel)
                    ->schema($leftSchema)
                    ->columns($innerColumns),
                SchemaComponents\Section::make($rightLabel)
                    ->schema($rightSchema)
                    ->columns($innerColumns),
            ])->from('lg'),
        ];
    }
}
