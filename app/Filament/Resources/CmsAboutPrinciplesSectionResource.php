<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsAboutPrinciplesSectionResource\Pages\EditCmsAboutPrinciplesSection;
use App\Models\CmsAboutPrinciplesSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsAboutPrinciplesSectionResource extends Resource
{
    protected static ?string $model = CmsAboutPrinciplesSection::class;

    protected static ?string $navigationLabel = 'Principles';

    protected static ?string $modelLabel = 'about principles';

    protected static ?string $pluralModelLabel = 'about principles';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'About';

    protected static ?int $navigationSort = 23;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Principles header')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('heading')
                            ->label('Heading')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                SchemaComponents\Section::make('Principle cards')
                    ->schema([
                        Components\Repeater::make('principles')
                            ->label('Cards')
                            ->schema([
                                Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Components\Textarea::make('description')
                                    ->label('Description')
                                    ->required()
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
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsAboutPrinciplesSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsAboutPrinciplesSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
