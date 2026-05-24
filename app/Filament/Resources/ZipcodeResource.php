<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZipcodeResource\Pages;
use App\Models\Zipcode;
use Filament\Actions;
use Filament\Forms\Components;
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

    protected static ?string $navigationLabel = 'Zipcodes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
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
                Actions\EditAction::make()
                    ->modalHeading('Edit Zipcode')
                    ->modalWidth('5xl')
                    ->modalSubmitActionLabel('Save'),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
