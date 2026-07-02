<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\AboutMissionPresenter;
use Filament\Forms\Components;

class AboutMissionBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::AboutMission;
    }

    public static function icon(): string
    {
        return 'heroicon-o-flag';
    }

    public static function schema(): array
    {
        return [
            Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255),
            Components\TextInput::make('headline')->label('Headline')->required()->maxLength(255)->columnSpanFull(),
            Components\Textarea::make('intro_text')->label('Intro')->rows(3)->maxLength(5000)->columnSpanFull(),
            Components\Textarea::make('body_middle')->label('Body middle')->rows(3)->maxLength(5000)->columnSpanFull(),
            Components\Textarea::make('body_last')->label('Body last')->rows(3)->maxLength(5000)->columnSpanFull(),
            Components\FileUpload::make('image_path')->label('Image')->image()->disk('public')->directory('cms/about/mission')->visibility('public')->maxSize(8192)->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return AboutMissionPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.about-mission';
    }
}
