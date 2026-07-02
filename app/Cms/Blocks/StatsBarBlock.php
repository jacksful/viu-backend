<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\StatsBarPresenter;
use Filament\Forms\Components;

class StatsBarBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::StatsBar;
    }

    public static function icon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function schema(): array
    {
        return [
            Components\Repeater::make('items')
                ->label('Stats')
                ->schema([
                    Components\TextInput::make('value')
                        ->label('Value')
                        ->required()
                        ->maxLength(32),
                    Components\TextInput::make('label')
                        ->label('Label')
                        ->required()
                        ->maxLength(255),
                ])
                ->defaultItems(3)
                ->minItems(1)
                ->maxItems(6)
                ->reorderable()
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return StatsBarPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.stats-bar';
    }
}
