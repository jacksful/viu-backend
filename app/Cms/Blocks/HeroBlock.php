<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\HeroPresenter;
use App\Cms\Support\PageRenderContext;
use Filament\Forms\Components;

class HeroBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::Hero;
    }

    public static function icon(): string
    {
        return 'heroicon-o-photo';
    }

    public static function schema(): array
    {
        return [
            Components\Textarea::make('title')
                ->label('Title')
                ->required()
                ->rows(2)
                ->maxLength(255)
                ->helperText('Use Enter for a second headline line.')
                ->columnSpanFull(),
            Components\Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->maxLength(5000)
                ->columnSpanFull(),
            Components\FileUpload::make('image_path')
                ->label('Background image')
                ->image()
                ->disk('public')
                ->directory('cms/hero')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(5120)
                ->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return HeroPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.hero';
    }

    public static function renderProps(array $content, PageRenderContext $context): array
    {
        return [
            'hero' => static::presenter($content),
        ];
    }
}
