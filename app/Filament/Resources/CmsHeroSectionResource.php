<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsHeroSectionResource\Pages\EditCmsHeroSection;
use App\Models\CmsHeroSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsHeroSectionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = CmsHeroSection::class;

    protected static ?string $navigationLabel = 'Hero Section';

    protected static ?string $modelLabel = 'hero section';

    protected static ?string $pluralModelLabel = 'hero section';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 11;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Hero')
                    ->schema([
                        Components\Textarea::make('title')
                            ->label('Title')
                            ->required()
                            ->rows(2)
                            ->maxLength(255)
                            ->helperText('Use one line, or two lines separated by Enter for the stacked headline.')
                            ->columnSpanFull(),

                        Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/hero')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->helperText('Recommended: wide image (e.g. 1920×800). Max 5 MB.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsHeroSection::route('{record}/edit'),
        ];
    }

    /**
     * Index route is a record-scoped edit URL; always inject the singleton record key.
     *
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsHeroSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
