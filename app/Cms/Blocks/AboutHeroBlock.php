<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\AboutHeroPresenter;
use Filament\Forms\Components;

class AboutHeroBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::AboutHero;
    }

    public static function icon(): string
    {
        return 'heroicon-o-sparkles';
    }

    public static function schema(): array
    {
        return [
            Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255),
            Components\Textarea::make('title')->label('Title')->required()->rows(2)->maxLength(500)->helperText('Use Enter for a line break.')->columnSpanFull(),
            Components\Textarea::make('lead')->label('Lead paragraph')->rows(4)->maxLength(5000)->columnSpanFull(),
            Components\FileUpload::make('image_path')->label('Image')->image()->disk('public')->directory('cms/about/hero')->visibility('public')->maxSize(5120)->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return AboutHeroPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.about-hero';
    }
}
