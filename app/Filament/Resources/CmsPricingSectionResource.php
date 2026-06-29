<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsPricingSectionResource\Pages\EditCmsPricingSection;
use App\Models\CmsPricingSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsPricingSectionResource extends Resource
{
    protected static ?string $model = CmsPricingSection::class;

    protected static ?string $navigationLabel = 'Pricing Section';

    protected static ?string $modelLabel = 'Pricing Section';

    protected static ?string $pluralModelLabel = 'Pricing Section';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 15;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Left column (image & price card)')
                    ->schema([
                        Components\FileUpload::make('left_image_path')
                            ->label('Background image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/pricing')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(8192)
                            ->helperText('Leave empty for default padlock image.')
                            ->columnSpanFull(),

                        Components\TextInput::make('card_label_starting')
                            ->label('Card top label (e.g. Starting)')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_price_display')
                            ->label('Price (large, e.g. $199)')
                            ->required()
                            ->maxLength(64),

                        Components\TextInput::make('card_price_period')
                            ->label('Price suffix (e.g. /mo)')
                            ->required()
                            ->maxLength(32),

                        Components\TextInput::make('card_per_label')
                            ->label('Per-line label')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('card_footer_note')
                            ->label('Card footer note (italic)')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Right column (copy & CTA)')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\TextInput::make('heading')
                            ->label('Heading')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('intro')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\Repeater::make('feature_lines')
                            ->label('Bullet list')
                            ->schema([
                                Components\Textarea::make('text')
                                    ->label('Line')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(4)
                            ->minItems(1)
                            ->maxItems(12)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),

                        Components\TextInput::make('cta_label')
                            ->label('Button label')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('cta_href')
                            ->label('Button link')
                            ->required()
                            ->maxLength(2048)
                            ->helperText('Anchor (e.g. #hero-zip) or full URL.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsPricingSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsPricingSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
