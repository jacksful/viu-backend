<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\FaqPresenter;
use Filament\Forms\Components;
use Filament\Schemas\Components as SchemaComponents;

class FaqBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::Faq;
    }

    public static function icon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function schema(): array
    {
        return [
            SchemaComponents\Section::make('Header & support')->schema([
                Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255),
                Components\TextInput::make('heading')->label('Heading')->required()->maxLength(255),
                Components\Textarea::make('intro')->label('Description')->rows(3)->maxLength(5000)->columnSpanFull(),
                Components\TextInput::make('support_label')->label('Support label')->required()->maxLength(255),
                Components\TextInput::make('support_email')->label('Support email')->email()->required()->maxLength(255),
                Components\FileUpload::make('support_icon_path')->label('Support icon')->image()->disk('public')->directory('cms/qa/icons')->visibility('public')->maxSize(1024)->columnSpanFull(),
            ])->columns(2),
            SchemaComponents\Section::make('FAQ accordion')->schema([
                Components\Repeater::make('faq_items')
                    ->label('Questions')
                    ->schema([
                        Components\TextInput::make('question')->label('Question')->required()->maxLength(500)->columnSpanFull(),
                        Components\Textarea::make('answer')->label('Answer')->required()->rows(4)->maxLength(8000)->columnSpanFull(),
                        Components\Toggle::make('opened')->label('Open by default')->default(false),
                    ])
                    ->defaultItems(4)
                    ->minItems(1)
                    ->maxItems(30)
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ]),
        ];
    }

    public static function presenter(array $content): object
    {
        return FaqPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.faq-section';
    }
}
