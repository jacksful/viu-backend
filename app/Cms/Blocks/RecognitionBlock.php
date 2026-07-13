<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\RecognitionPresenter;
use Filament\Forms\Components;

class RecognitionBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::Recognition;
    }

    public static function icon(): string
    {
        return 'heroicon-o-star';
    }

    public static function schema(): array
    {
        return static::sideBySideColumns(
            'Left column',
            [
                Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
                Components\TextInput::make('headline_line_1')->label('Headline line 1')->required()->maxLength(255),
                Components\TextInput::make('headline_line_2')->label('Headline line 2')->required()->maxLength(255),
                Components\TextInput::make('headline_line_3')->label('Headline line 3')->required()->maxLength(255),
                Components\Textarea::make('intro')->label('Intro')->rows(3)->maxLength(5000)->columnSpanFull(),
                Components\TextInput::make('box_top_left')->label('Box top left')->required()->maxLength(500),
                Components\TextInput::make('box_top_right')->label('Box top right')->required()->maxLength(500),
                Components\Textarea::make('box_wide_body')->label('Wide box body')->rows(2)->maxLength(1000)->columnSpanFull(),
                Components\TextInput::make('box_wide_accent')->label('Wide box accent tag')->maxLength(255),
            ],
            'Right column',
            [
                Components\FileUpload::make('right_image_path')->label('Image')->image()->disk('public')->directory('cms/recognition')->visibility('public')->maxSize(8192)->columnSpanFull(),
                Components\FileUpload::make('card_logo_path')->label('Card logo')->image()->disk('public')->directory('cms/recognition/logo')->visibility('public')->maxSize(1024)->columnSpanFull(),
                Components\TextInput::make('card_kicker')->label('Card kicker')->maxLength(255),
                Components\TextInput::make('card_title')->label('Card title')->maxLength(255),
                Components\TextInput::make('card_progress_label_left')->label('Progress label left')->maxLength(255),
                Components\TextInput::make('card_progress_label_right')->label('Progress label right')->maxLength(255),
                Components\TextInput::make('card_progress_percent')->label('Progress percent')->numeric()->minValue(0)->maxValue(100),
            ],
        );
    }

    public static function presenter(array $content): object
    {
        return RecognitionPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.recognition-section';
    }
}
