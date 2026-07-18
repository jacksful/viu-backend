<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsStrategicWindowSectionResource\Pages\EditCmsStrategicWindowSection;
use App\Models\CmsStrategicWindowSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsStrategicWindowSectionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = CmsStrategicWindowSection::class;

    protected static ?string $navigationLabel = 'Strategic window';

    protected static ?string $modelLabel = 'Strategic window';

    protected static ?string $pluralModelLabel = 'Strategic window';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 12;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        SchemaComponents\Section::make('Left column')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge')
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
                            ->label('Intro paragraph')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\Repeater::make('features')
                            ->label('Feature list')
                            ->schema([
                                Components\TextInput::make('icon_path')
                                    ->label('Icon path')
                                    ->maxLength(500)
                                    ->helperText('File under `public/` (e.g. `image/Container.png`) or under storage after upload (e.g. `cms/strategic-window/foo.png` on the public disk).')
                                    ->columnSpanFull(),

                                Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),

                                Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->maxLength(2000)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(3)
                            ->minItems(1)
                            ->maxItems(6)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                        SchemaComponents\Section::make('Right column (image & card)')
                    ->schema([
                        Components\FileUpload::make('visual_image_path')
                            ->label('Background image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/strategic-window')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(8192)
                            ->helperText('Large photo behind the card (e.g. 1200×900). Max 8 MB.')
                            ->columnSpanFull(),

                        Components\FileUpload::make('card_icon_path')
                            ->label('Card pulse icon')
                            ->image()
                            ->disk('public')
                            ->directory('cms/strategic-window/card')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->columnSpanFull(),

                        Components\TextInput::make('card_kicker')
                            ->label('Card kicker (small caps)')
                            ->maxLength(255),

                        Components\TextInput::make('card_title')
                            ->label('Card title')
                            ->maxLength(255),

                        Components\TextInput::make('card_metric_label')
                            ->label('Metric label')
                            ->maxLength(255),

                        Components\TextInput::make('card_metric_percent')
                            ->label('Metric percent')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        Components\Textarea::make('card_quote')
                            ->label('Footer quote')
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
            'index' => EditCmsStrategicWindowSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsStrategicWindowSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
