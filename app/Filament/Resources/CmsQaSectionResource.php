<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsQaSectionResource\Pages\EditCmsQaSection;
use App\Models\CmsQaSection;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CmsQaSectionResource extends Resource
{
    protected static ?string $model = CmsQaSection::class;

    protected static ?string $navigationLabel = 'QA Section';

    protected static ?string $modelLabel = 'QA Section';

    protected static ?string $pluralModelLabel = 'QA Section';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?string $navigationParentItem = 'Home Page';

    protected static ?int $navigationSort = 16;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Header & support')
                    ->schema([
                        Components\TextInput::make('badge_text')
                            ->label('Badge / pill')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('heading')
                            ->label('Heading')
                            ->required()
                            ->maxLength(255),

                        Components\Textarea::make('intro')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Components\TextInput::make('support_label')
                            ->label('Support label')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('support_email')
                            ->label('Support email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Components\FileUpload::make('support_icon_path')
                            ->label('Support icon')
                            ->image()
                            ->disk('public')
                            ->directory('cms/qa/icons')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->helperText('Optional. Default: bundled envelope icon.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('FAQ accordion')
                    ->schema([
                        Components\Repeater::make('faq_items')
                            ->label('Questions')
                            ->schema([
                                Components\TextInput::make('question')
                                    ->label('Question')
                                    ->required()
                                    ->maxLength(500)
                                    ->columnSpanFull(),

                                Components\Textarea::make('answer')
                                    ->label('Answer')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(8000)
                                    ->columnSpanFull(),

                                Components\Toggle::make('opened')
                                    ->label('Open by default')
                                    ->helperText('The first item with this on stays expanded on load (use one).')
                                    ->default(false),
                            ])
                            ->defaultItems(4)
                            ->minItems(1)
                            ->maxItems(30)
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EditCmsQaSection::route('{record}/edit'),
        ];
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($name) || $name === 'index') {
            $parameters['record'] ??= CmsQaSection::singleton()->getRouteKey();
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
