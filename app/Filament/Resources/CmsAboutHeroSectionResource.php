<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsAboutHeroSectionResource\Pages\EditCmsAboutHeroSection;
use App\Models\CmsAboutHeroSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsAboutHeroSectionResource extends Resource
{
    protected static ?string $model = CmsAboutHeroSection::class;

    protected static ?string $navigationLabel = 'Hero';

    protected static ?string $modelLabel = 'about hero';

    protected static ?string $pluralModelLabel = 'about hero';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'About';

    protected static ?int $navigationSort = 21;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('About hero')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('title')
                            ->label('Title')
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('Use Enter for a line break in the headline.')
                            ->columnSpanFull(),

                        Components\Textarea::make('lead')
                            ->label('Lead paragraph')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/about/hero')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->helperText('Optional hero background. Recommended: wide image (e.g. 1920×800). Max 5 MB.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsAboutHeroSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsAboutHeroSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
