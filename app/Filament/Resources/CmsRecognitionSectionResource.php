<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsRecognitionSectionResource\Pages\EditCmsRecognitionSection;
use App\Models\CmsRecognitionSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsRecognitionSectionResource extends Resource
{
    protected static ?string $model = CmsRecognitionSection::class;

    protected static ?string $navigationLabel = 'Brand authority';

    protected static ?string $modelLabel = 'Brand authority';

    protected static ?string $pluralModelLabel = 'Brand authority';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 14;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Left column')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge / pill')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\TextInput::make('headline_line_1')
                            ->label('Headline line 1')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('headline_line_2')
                            ->label('Headline line 2')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('headline_line_3')
                            ->label('Headline line 3')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('intro')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\TextInput::make('box_top_left')
                            ->label('Top-left box')
                            ->required()
                            ->maxLength(500),

                        Components\TextInput::make('box_top_right')
                            ->label('Top-right box')
                            ->required()
                            ->maxLength(500),

                        Components\Textarea::make('box_wide_body')
                            ->label('Wide box — main text')
                            ->required()
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Components\TextInput::make('box_wide_accent')
                            ->label('Wide box — accent (orange caps)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Right column (photo & card)')
                    ->schema([
                        Components\FileUpload::make('right_image_path')
                            ->label('Photo')
                            ->image()
                            ->disk('public')
                            ->directory('cms/recognition')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(8192)
                            ->helperText('Leave empty for default `build-for` asset.')
                            ->columnSpanFull(),

                        Components\FileUpload::make('card_logo_path')
                            ->label('Card logo')
                            ->image()
                            ->disk('public')
                            ->directory('cms/recognition/logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Leave empty for default VIU logo.')
                            ->columnSpanFull(),

                        Components\TextInput::make('card_kicker')
                            ->label('Card kicker')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_title')
                            ->label('Card title')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_progress_label_left')
                            ->label('Progress bar — left label')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_progress_label_right')
                            ->label('Progress bar — right label')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('card_progress_percent')
                            ->label('Progress fill')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsRecognitionSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsRecognitionSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
