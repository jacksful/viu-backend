<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsAboutMissionSectionResource\Pages\EditCmsAboutMissionSection;
use App\Models\CmsAboutMissionSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsAboutMissionSectionResource extends Resource
{
    protected static ?string $model = CmsAboutMissionSection::class;

    protected static ?string $navigationLabel = 'Mission';

    protected static ?string $modelLabel = 'about mission';

    protected static ?string $pluralModelLabel = 'about mission';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'About';

    protected static ?int $navigationSort = 22;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Mission')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('headline')
                            ->label('Headline')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Components\Textarea::make('intro_text')
                            ->label('Intro paragraph')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\Textarea::make('body_middle')
                            ->label('Middle paragraph')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\Textarea::make('body_last')
                            ->label('Closing paragraph')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/about/mission')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsAboutMissionSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsAboutMissionSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
