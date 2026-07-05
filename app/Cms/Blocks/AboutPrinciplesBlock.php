<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\AboutPrinciplesPresenter;
use Filament\Forms\Components;

class AboutPrinciplesBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::AboutPrinciples;
    }

    public static function icon(): string
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function schema(): array
    {
        return [
            Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
            Components\TextInput::make('heading')->label('Heading')->required()->maxLength(255)->columnSpanFull(),
            Components\Repeater::make('principles')
                ->label('Principles')
                ->schema([
                    Components\TextInput::make('title')->label('Title')->required()->maxLength(255),
                    Components\Textarea::make('description')->label('Description')->required()->rows(3)->maxLength(2000)->columnSpanFull(),
                ])
                ->defaultItems(3)
                ->minItems(1)
                ->maxItems(12)
                ->reorderable()
                ->collapsible()
                ->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return AboutPrinciplesPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.about-principles';
    }
}
