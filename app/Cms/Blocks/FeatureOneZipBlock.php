<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\TerritoryZipPresenter;
use Filament\Forms\Components;

class FeatureOneZipBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::FeatureOneZip;
    }

    public static function icon(): string
    {
        return 'heroicon-o-map-pin';
    }

    public static function schema(): array
    {
        return static::sideBySideColumns(
            'Left column',
            [
                Components\FileUpload::make('left_visual_image_path')->label('Background image')->image()->disk('public')->directory('cms/territory-zip')->visibility('public')->maxSize(8192)->columnSpanFull(),
                Components\FileUpload::make('left_card_icon_path')->label('Card icon')->image()->disk('public')->directory('cms/territory-zip/card')->visibility('public')->maxSize(1024)->columnSpanFull(),
                Components\TextInput::make('card_kicker')->label('Card kicker')->maxLength(255),
                Components\TextInput::make('card_title')->label('Card title')->maxLength(255),
                Components\Repeater::make('checklist_items')
                    ->label('Checklist')
                    ->schema([
                        Components\TextInput::make('text')->label('Line')->required()->maxLength(255),
                    ])
                    ->defaultItems(4)
                    ->minItems(1)
                    ->reorderable()
                    ->columnSpanFull(),
            ],
            'Right column',
            [
                Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
                Components\TextInput::make('headline_primary')->label('Headline (navy line)')->required()->maxLength(255),
                Components\TextInput::make('headline_accent')->label('Headline (accent line)')->required()->maxLength(255),
                Components\Textarea::make('intro')->label('Intro')->rows(4)->maxLength(5000)->columnSpanFull(),
                Components\Repeater::make('feature_columns')
                    ->label('Exclusivity columns')
                    ->schema([
                        Components\TextInput::make('icon_path')->label('Icon path')->maxLength(500),
                        Components\TextInput::make('label')->label('Label')->required()->maxLength(255),
                    ])
                    ->defaultItems(3)
                    ->minItems(1)
                    ->maxItems(6)
                    ->reorderable()
                    ->columnSpanFull(),
                Components\FileUpload::make('quote_icon_path')->label('Quote icon')->image()->disk('public')->directory('cms/territory-zip/quote')->visibility('public')->maxSize(1024)->columnSpanFull(),
                Components\Textarea::make('quote_text')->label('Quote text')->rows(2)->maxLength(1000)->columnSpanFull(),
            ],
        );
    }

    public static function presenter(array $content): object
    {
        return TerritoryZipPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.feature-one-zip';
    }
}
