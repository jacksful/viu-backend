<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZipcodeResource\Pages;
use App\Models\Zipcode;
use App\Services\ZipcodeStripePriceService;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZipcodeResource extends Resource
{
    protected static ?string $model = Zipcode::class;

    protected static ?string $navigationLabel = 'Territories';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Market';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map-pin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Zipcode Information')
                    ->schema([
                        Components\TextInput::make('code')
                            ->label('Zipcode')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter zipcode (e.g., 12345)'),

                        Components\TextInput::make('city')
                            ->label('City')
                            ->maxLength(255)
                            ->placeholder('Enter city name'),

                        Components\TextInput::make('state')
                            ->label('State')
                            ->maxLength(255)
                            ->placeholder('Enter state name'),

                        Components\TextInput::make('area')
                            ->label('Area')
                            ->nullable()
                            ->maxLength(255)
                            ->placeholder('Enter area name'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Pricing')
                    ->schema([
                        Components\TextInput::make('monthly_price')
                            ->label('Monthly Price')
                            ->numeric()
                            ->prefix('$')
                            ->nullable()
                            ->step(0.01)
                            ->placeholder('Optional'),

                        Components\TextInput::make('yearly_price')
                            ->label('Yearly Price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->step(0.01)
                            ->required()
                            ->placeholder('0.00'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Stripe')
                    ->description('Create Stripe prices after saving zipcode pricing. Yearly price maps to stripe_price_id; monthly price maps to stripe_monthly_price_id.')
                    ->schema([
                        Components\TextInput::make('stripe_product_id')
                            ->label('Stripe Product ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Not created yet'),

                        Components\TextInput::make('stripe_price_id')
                            ->label('Stripe Yearly Price ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Not created yet'),

                        Components\TextInput::make('stripe_monthly_price_id')
                            ->label('Stripe Monthly Price ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Not created yet'),
                    ])
                    ->columns(2)
                    ->visible(fn (?Zipcode $record): bool => $record !== null),

                Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Zipcode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('area')
                    ->label('Area')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('monthly_price')
                    ->label('Monthly Price')
                    ->formatStateUsing(fn ($state): string => $state === null || $state === '' ? '—' : '$'.number_format((float) $state, 2))
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('yearly_price')
                    ->label('Yearly Price')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\IconColumn::make('stripe_price_id')
                    ->label('Stripe Price')
                    ->boolean()
                    ->getStateUsing(fn (Zipcode $record): bool => filled($record->stripe_price_id))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (Zipcode $record): string => filled($record->stripe_price_id)
                        ? "Yearly: {$record->stripe_price_id}"
                        : 'Stripe price not created'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All zipcodes')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('createStripePrice')
                    ->label('Create Stripe Price')
                    ->icon('heroicon-o-credit-card')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Create Stripe Prices')
                    ->modalDescription(fn (Zipcode $record): string => "Create Stripe product and price objects for ZIP {$record->code}. Existing prices are reused when amount and interval match.")
                    ->visible(fn (Zipcode $record): bool => $record->hasBillingPlans())
                    ->action(function (Zipcode $record, ZipcodeStripePriceService $stripePrices): void {
                        try {
                            $result = $stripePrices->syncPrices($record->fresh());

                            $created = collect([
                                filled($result['yearly_price_id']) ? "Yearly: {$result['yearly_price_id']}" : null,
                                filled($result['monthly_price_id']) ? "Monthly: {$result['monthly_price_id']}" : null,
                            ])->filter()->implode("\n");

                            Notification::make()
                                ->title('Stripe prices synced')
                                ->body($created !== '' ? $created : 'Stripe product updated.')
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Stripe price creation failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Actions\EditAction::make()
                    ->modalHeading('Edit Zipcode')
                    ->modalWidth('5xl')
                    ->modalSubmitActionLabel('Save'),
                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete zipcode')
                    ->modalDescription('Are you sure you want to delete this zipcode? This action cannot be undone.'),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected zipcodes')
                        ->modalDescription('Are you sure you want to delete the selected zipcodes? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageZipcodes::route(''),
        ];
    }
}
