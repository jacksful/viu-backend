<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\CtaBannerPresenter;
use Filament\Forms\Components;

class CtaBannerBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::CtaBanner;
    }

    public static function icon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public static function schema(): array
    {
        return [
            Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
            Components\TextInput::make('title')->label('Title')->required()->maxLength(255)->columnSpanFull(),
            Components\Textarea::make('text')->label('Description')->rows(3)->maxLength(2000)->columnSpanFull(),
            Components\TextInput::make('primary_button_label')->label('Primary button label')->required()->maxLength(255),
            Components\TextInput::make('secondary_button_label')->label('Secondary button label')->required()->maxLength(255),
        ];
    }

    public static function presenter(array $content): object
    {
        return CtaBannerPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.cta-banner';
    }
}
