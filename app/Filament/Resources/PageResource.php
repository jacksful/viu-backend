<?php

namespace App\Filament\Resources;

use App\Cms\Enums\PageMenuPosition;
use App\Cms\Enums\PageRobots;
use App\Cms\Enums\PageStatus;
use App\Cms\Support\BlockRegistry;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'page';

    protected static ?string $pluralModelLabel = 'pages';

    protected static string|\UnitEnum|null $navigationGroup = 'CMS';

    protected static ?int $navigationSort = 5;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                SchemaComponents\Tabs::make('Page')
                    ->columnSpanFull()
                    ->tabs([
                        SchemaComponents\Tabs\Tab::make('General')
                            ->schema([
                                Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set, ?Page $record): void {
                                        if ($record !== null) {
                                            return;
                                        }

                                        $set('slug', Str::slug((string) $state));
                                    }),

                                Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->alphaDash()
                                    ->helperText('Used in the public URL. Cannot match reserved system paths.'),

                                Components\Select::make('status')
                                    ->options(collect(PageStatus::cases())->mapWithKeys(
                                        fn (PageStatus $status) => [$status->value => $status->label()]
                                    )->all())
                                    ->required()
                                    ->default(PageStatus::Draft->value),

                                Components\Toggle::make('is_homepage')
                                    ->label('Homepage')
                                    ->helperText('Only one page can be the homepage (served at /).'),

                                Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Admin list ordering only.'),

                                Components\TextInput::make('body_class')
                                    ->label('Body CSS class')
                                    ->maxLength(255)
                                    ->helperText('Optional. Use "legal-page" for legal layouts.'),

                                Components\TextInput::make('menu_label')
                                    ->label('Menu label')
                                    ->maxLength(255)
                                    ->helperText('Optional. Falls back to page title in navigation.'),

                                Components\TextInput::make('menu_sort_order')
                                    ->label('Menu sort order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first within each menu.'),

                                Components\CheckboxList::make('menu_positions')
                                    ->label('Menu positions')
                                    ->options(collect(PageMenuPosition::cases())->mapWithKeys(
                                        fn (PageMenuPosition $position) => [$position->value => $position->label()]
                                    )->all())
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        SchemaComponents\Tabs\Tab::make('SEO')
                            ->schema([
                                SchemaComponents\Section::make('Basic meta')
                                    ->schema([
                                        Components\TextInput::make('seo_title')
                                            ->label('Meta title')
                                            ->maxLength(70)
                                            ->helperText('Shown in browser tabs and search results. Falls back to page title.'),

                                        Components\Textarea::make('seo_description')
                                            ->label('Meta description')
                                            ->rows(3)
                                            ->maxLength(160),

                                        Components\Textarea::make('meta_keywords')
                                            ->label('Meta keywords')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Optional. Google does not use keywords for ranking.'),

                                        Components\TextInput::make('canonical_url')
                                            ->label('Canonical URL')
                                            ->url()
                                            ->maxLength(2048)
                                            ->helperText('Leave empty to auto-generate from the page URL.'),

                                        Components\Select::make('robots')
                                            ->label('Robots')
                                            ->options(collect(PageRobots::cases())->mapWithKeys(
                                                fn (PageRobots $robots) => [$robots->value => $robots->label()]
                                            )->all())
                                            ->default(PageRobots::IndexFollow->value)
                                            ->required(),
                                    ])
                                    ->columns(2),

                                SchemaComponents\Section::make('Open Graph')
                                    ->schema([
                                        Components\TextInput::make('og_title')
                                            ->label('Open Graph title')
                                            ->maxLength(255)
                                            ->helperText('Falls back to meta title.'),

                                        Components\Textarea::make('og_description')
                                            ->label('Open Graph description')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Falls back to meta description.'),

                                        Components\FileUpload::make('og_image_path')
                                            ->label('Open Graph image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('cms/pages/og')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->helperText('Recommended: 1200×630. Used when sharing on Facebook, LinkedIn, etc.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                SchemaComponents\Section::make('Twitter card')
                                    ->schema([
                                        Components\TextInput::make('twitter_title')
                                            ->label('Twitter card title')
                                            ->maxLength(255)
                                            ->helperText('Falls back to Open Graph title.'),

                                        Components\Textarea::make('twitter_description')
                                            ->label('Twitter card description')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Falls back to Open Graph description.'),

                                        Components\FileUpload::make('twitter_image_path')
                                            ->label('Twitter card image')
                                            ->image()
                                            ->disk('public')
                                            ->directory('cms/pages/twitter')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(5120)
                                            ->helperText('Falls back to Open Graph image if empty.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                SchemaComponents\Section::make('Custom meta tags')
                                    ->schema([
                                        Components\Repeater::make('meta_tags')
                                            ->label('Additional meta tags')
                                            ->schema([
                                                Components\Select::make('type')
                                                    ->label('Attribute')
                                                    ->options([
                                                        'name' => 'name',
                                                        'property' => 'property',
                                                    ])
                                                    ->default('name')
                                                    ->required(),

                                                Components\TextInput::make('key')
                                                    ->label('Key')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('e.g. author'),

                                                Components\TextInput::make('value')
                                                    ->label('Content')
                                                    ->required()
                                                    ->maxLength(2048),
                                            ])
                                            ->columns(3)
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->maxItems(20)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        SchemaComponents\Tabs\Tab::make('Content')
                            ->schema([
                                Components\Builder::make('sections')
                                    ->label('Page sections')
                                    ->blocks(BlockRegistry::filamentBlocks())
                                    ->blockNumbers(false)
                                    ->cloneable()
                                    ->collapsible()
                                    ->reorderableWithDragAndDrop()
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PageStatus $state): string => $state->label())
                    ->color(fn (PageStatus $state): string => match ($state) {
                        PageStatus::Published => 'success',
                        PageStatus::Draft => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_homepage')
                    ->label('Home')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(PageStatus::cases())->mapWithKeys(
                        fn (PageStatus $status) => [$status->value => $status->label()]
                    )->all()),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Page $record): string => URL::temporarySignedRoute(
                        'pages.preview',
                        now()->addHour(),
                        ['page' => $record]
                    ))
                    ->openUrlInNewTab(),

                Actions\EditAction::make(),

                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('sections');
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
