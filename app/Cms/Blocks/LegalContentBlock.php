<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\LegalContentPresenter;
use Filament\Forms\Components;

class LegalContentBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::LegalContent;
    }

    public static function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function schema(): array
    {
        return [
            Components\Textarea::make('lead')
                ->label('Lead paragraph')
                ->rows(3)
                ->maxLength(5000)
                ->columnSpanFull(),
            Components\RichEditor::make('body')
                ->label('Content')
                ->required()
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'link',
                    'h2',
                    'h3',
                    'bulletList',
                    'orderedList',
                    'blockquote',
                ])
                ->columnSpanFull(),
        ];
    }

    public static function presenter(array $content): object
    {
        return LegalContentPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.legal-content';
    }
}
