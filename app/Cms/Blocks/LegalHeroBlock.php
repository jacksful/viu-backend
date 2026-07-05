<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\LegalHeroPresenter;
use Filament\Forms\Components;

class LegalHeroBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::LegalHero;
    }

    public static function icon(): string
    {
        return 'heroicon-o-scale';
    }

    public static function schema(): array
    {
        return [
            Components\TextInput::make('badge_text')
                ->label('Badge')
                ->default('Legal')
                ->required()
                ->maxLength(255),
            Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Components\TextInput::make('last_updated')
                ->label('Last updated label')
                ->placeholder('17 June 2026')
                ->maxLength(255)
                ->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return LegalHeroPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.legal-hero';
    }
}
