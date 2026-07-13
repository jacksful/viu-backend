<?php

namespace App\Cms\Blocks;

use App\Cms\Enums\PageBlockType;
use App\Cms\Presenters\PricingPresenter;
use App\Cms\Support\PageRenderContext;
use Filament\Forms\Components;

class PricingBlock extends AbstractPageBlock
{
    public static function type(): PageBlockType
    {
        return PageBlockType::Pricing;
    }

    public static function icon(): string
    {
        return 'heroicon-o-currency-dollar';
    }

    public static function schema(): array
    {
        return static::sideBySideColumns(
            'Left column',
            [
                Components\FileUpload::make('left_image_path')->label('Background image')->image()->disk('public')->directory('cms/pricing')->visibility('public')->maxSize(8192)->columnSpanFull(),
                Components\TextInput::make('card_label_starting')->label('Card top label')->required()->maxLength(255),
                Components\TextInput::make('card_price_display')->label('Price display')->required()->maxLength(64),
                Components\TextInput::make('card_price_period')->label('Price period')->required()->maxLength(32),
                Components\TextInput::make('card_per_label')->label('Per-line label')->required()->maxLength(255),
                Components\Textarea::make('card_footer_note')->label('Card footer note')->rows(2)->maxLength(1000)->columnSpanFull(),
            ],
            'Right column',
            [
                Components\TextInput::make('badge_text')->label('Badge')->required()->maxLength(255)->columnSpanFull(),
                Components\TextInput::make('heading')->label('Heading')->required()->maxLength(255),
                Components\Textarea::make('intro')->label('Description')->rows(3)->maxLength(5000)->columnSpanFull(),
                Components\Repeater::make('feature_lines')
                    ->label('Bullet list')
                    ->schema([
                        Components\Textarea::make('text')->label('Line')->required()->rows(2)->maxLength(1000)->columnSpanFull(),
                    ])
                    ->defaultItems(4)
                    ->minItems(1)
                    ->maxItems(12)
                    ->reorderable()
                    ->columnSpanFull(),
                Components\TextInput::make('cta_label')->label('Button label')->required()->maxLength(255),
                Components\TextInput::make('cta_href')->label('Button link')->required()->maxLength(2048),
            ],
        );
    }

    public static function presenter(array $content): object
    {
        return PricingPresenter::from($content);
    }

    public static function view(): string
    {
        return 'components.pricing-section';
    }

    public static function renderProps(array $content, PageRenderContext $context): array
    {
        return [
            'section' => static::presenter($content),
            'zipcodes' => $context->zipcodes(),
        ];
    }
}
