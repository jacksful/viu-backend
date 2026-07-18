<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsTerritoryZipSectionResource\Pages\EditCmsTerritoryZipSection;
use App\Models\CmsTerritoryZipSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsTerritoryZipSectionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = CmsTerritoryZipSection::class;

    protected static ?string $navigationLabel = 'Territory lock';

    protected static ?string $modelLabel = 'Territory lock';

    protected static ?string $pluralModelLabel = 'Territory lock';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 13;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        SchemaComponents\Section::make('Left column (image & status card)')
                    ->schema([
                        Components\FileUpload::make('left_visual_image_path')
                            ->label('Background image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/territory-zip')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(8192)
                            ->helperText('Wide landscape photo. Leave empty to use the default asset.')
                            ->columnSpanFull(),

                        Components\FileUpload::make('left_card_icon_path')
                            ->label('Card header icon')
                            ->image()
                            ->disk('public')
                            ->directory('cms/territory-zip/card-icons')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->columnSpanFull(),

                        Components\TextInput::make('card_kicker')
                            ->label('Card kicker (e.g. ZIP TERRITORY: 90210)')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_title')
                            ->label('Card title')
                            ->required()
                            ->maxLength(255),

                        Components\Repeater::make('checklist_items')
                            ->label('Checklist lines')
                            ->schema([
                                Components\TextInput::make('text')
                                    ->label('Line')
                                    ->required()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(4)
                            ->minItems(1)
                            ->maxItems(12)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),

                        Components\FileUpload::make('checklist_check_icon_path')
                            ->label('Checklist row icon (optional)')
                            ->image()
                            ->disk('public')
                            ->directory('cms/territory-zip/checks')
                            ->visibility('public')
                            ->maxSize(512)
                            ->helperText('Square mark shown left of each line. Default: bundled check icon.')
                            ->columnSpanFull(),
                    ]),

                        SchemaComponents\Section::make('Right column (copy, icons, quote)')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Top badge')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\TextInput::make('headline_primary')
                            ->label('Headline (navy line)')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('headline_accent')
                            ->label('Headline (accent line)')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('intro')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\Repeater::make('feature_columns')
                            ->label('Three icon columns')
                            ->schema([
                                Components\TextInput::make('icon_path')
                                    ->label('Icon path')
                                    ->maxLength(500)
                                    ->helperText('`public/` path (e.g. `image/territory-ico1.png`) or storage path starting with `cms/`.')
                                    ->columnSpanFull(),

                                Components\TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(3)
                            ->minItems(1)
                            ->maxItems(6)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),

                        Components\FileUpload::make('quote_icon_path')
                            ->label('Quote bar icon')
                            ->image()
                            ->disk('public')
                            ->directory('cms/territory-zip/quote')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->columnSpanFull(),

                        Components\Textarea::make('quote_text')
                            ->label('Quote text')
                            ->rows(2)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsTerritoryZipSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsTerritoryZipSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
