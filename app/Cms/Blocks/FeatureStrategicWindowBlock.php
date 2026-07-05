<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\StrategicWindowPresenter;
use Filament\Forms\Components;
use Filament\Schemas\Components as SchemaComponents;

class FeatureStrategicWindowBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::FeatureStrategicWindow;
    }

    public static function icon(): string
    {
        return 'heroicon-o-sparkles';
    }

    public static function schema(): array
    {
        return [
            SchemaComponents\Section::make('Left column')->schema([
                Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
                Components\TextInput::make('headline_primary')->label('Headline (navy line)')->required()->maxLength(255),
                Components\TextInput::make('headline_accent')->label('Headline (accent line)')->required()->maxLength(255),
                Components\Textarea::make('intro')->label('Intro')->rows(4)->maxLength(5000)->columnSpanFull(),
                Components\Repeater::make('features')
                    ->label('Feature list')
                    ->schema([
                        Components\TextInput::make('icon_path')->label('Icon path')->maxLength(500)->columnSpanFull(),
                        Components\TextInput::make('title')->label('Title')->required()->maxLength(255),
                        Components\Textarea::make('description')->label('Description')->rows(3)->maxLength(2000)->columnSpanFull(),
                    ])
                    ->defaultItems(3)
                    ->minItems(1)
                    ->maxItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])->columns(2),
            SchemaComponents\Section::make('Right column')->schema([
                Components\FileUpload::make('visual_image_path')->label('Background image')->image()->disk('public')->directory('cms/strategic-window')->visibility('public')->maxSize(8192)->columnSpanFull(),
                Components\FileUpload::make('card_icon_path')->label('Card icon')->image()->disk('public')->directory('cms/strategic-window/card')->visibility('public')->maxSize(1024)->columnSpanFull(),
                Components\TextInput::make('card_kicker')->label('Card kicker')->maxLength(255),
                Components\TextInput::make('card_title')->label('Card title')->maxLength(255),
                Components\TextInput::make('card_metric_label')->label('Metric label')->maxLength(255),
                Components\TextInput::make('card_metric_percent')->label('Metric percent')->numeric()->minValue(0)->maxValue(100),
                Components\Textarea::make('card_quote')->label('Card quote')->rows(2)->maxLength(1000)->columnSpanFull(),
            ])->columns(2),
        ];
    }

    public static function presenter(array $content): object
    {
        return StrategicWindowPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.feature-be-first';
    }
}
