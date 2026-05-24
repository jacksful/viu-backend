<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserZipcodeSubscriptionResource\Pages;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserZipcodeSubscriptionResource extends Resource
{
    protected static ?string $model = UserZipcodeSubscription::class;

    protected static ?string $navigationLabel = 'Client Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Subscription Information')
                    ->schema([
                        Components\Select::make('user_id')
                            ->label('Client')
                            ->relationship('user', 'name', fn(Builder $query) => $query->where('role', 'customer'))
                            ->getOptionLabelFromRecordUsing(fn($record): string => "{$record->name} ({$record->email})")
                            ->searchable(['name', 'email', 'first_name', 'last_name'])
                            ->preload()
                            ->required()
                            ->placeholder('Select client')
                            ->helperText('Only customers can subscribe to zipcodes'),

                        Components\Select::make('zipcode_ids')
                            ->label('ZIP Codes')
                            ->options(function () {
                                return Zipcode::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($zipcode) {
                                        return [$zipcode->id => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}"];
                                    });
                            })
                            ->searchable()
                            ->multiple()
                            ->required()
                            ->placeholder('Select one or more ZIP codes')
                            ->helperText('Select multiple zipcodes for this subscription')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Subscription Period')
                    ->schema([
                        Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->placeholder('Select start date')
                            ->helperText('Subscription start date'),

                        Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->nullable()
                            ->displayFormat('Y-m-d')
                            ->placeholder('Select end date (optional)')
                            ->helperText('Leave empty for ongoing subscription')
                            ->after('start_date')
                            ->minDate(fn(callable $get) => $get('start_date') ?: now()),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Status')
                    ->schema([
                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'canceled' => 'Canceled',
                            ])
                            ->default('pending')
                            ->placeholder('Select status')
                            ->helperText('Current subscription status'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client Name')
                    ->searchable(['user.name', 'user.first_name', 'user.last_name', 'user.email'])
                    ->sortable()
                    ->formatStateUsing(
                        fn(UserZipcodeSubscription $record): string =>
                        $record->user ? $record->user->name : 'N/A'
                    ),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Client Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('zipcode_ids')
                    ->label('ZIP Codes')
                    ->getStateUsing(function (UserZipcodeSubscription $record): string {
                        return $record->zipcodes->map(function ($zipcode) {
                            return "ZIP {$zipcode->code}";
                        })->join(', ');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('zipcodes', function ($q) use ($search) {
                            $q->where('code', 'like', "%{$search}%")
                                ->orWhere('city', 'like', "%{$search}%")
                                ->orWhere('state', 'like', "%{$search}%");
                        });
                    })
                    ->wrap()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('zipcodes.location')
                    ->label('Locations')
                    ->getStateUsing(function (UserZipcodeSubscription $record): string {
                        if (empty($record->zipcode_ids)) {
                            return '—';
                        }

                        $zipcodes = Zipcode::whereIn('id', $record->zipcode_ids)->get();

                        if ($zipcodes->isEmpty()) {
                            return '—';
                        }

                        return $zipcodes->map(function ($zipcode) {
                            $city = $zipcode->city ?? '';
                            $state = $zipcode->state ?? '';
                            return $city && $state ? "{$city}, {$state}" : '';
                        })->filter()->join(' | ');
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Ongoing')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'expired',
                        'gray' => 'canceled',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'canceled' => 'Canceled',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Client')
                    ->relationship('user', 'name', fn(Builder $query) => $query->where('role', 'customer'))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('zipcode_id')
                    ->label('ZIP Code')
                    ->form([
                        Components\Select::make('zipcode_id')
                            ->label('ZIP Code')
                            ->options(function () {
                                return Zipcode::where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($zipcode) {
                                        return [$zipcode->id => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}"];
                                    });
                            })
                            ->searchable()
                            ->placeholder('Select ZIP code'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['zipcode_id'],
                            fn(Builder $query, $zipcodeId): Builder =>
                            $query->whereJsonContains('zipcode_ids', $zipcodeId)
                        );
                    }),

                Tables\Filters\Filter::make('start_date')
                    ->form([
                        Components\DatePicker::make('started_from')
                            ->label('Start Date From'),
                        Components\DatePicker::make('started_until')
                            ->label('Start Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['started_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['started_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('end_date')
                    ->label('Subscription Type')
                    ->placeholder('All subscriptions')
                    ->trueLabel('Ongoing (no end date)')
                    ->falseLabel('With end date')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNull('end_date'),
                        false: fn(Builder $query) => $query->whereNotNull('end_date'),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->actions([
                Actions\Action::make('toggleStatus')
                    ->label(
                        fn(UserZipcodeSubscription $record): string =>
                        match ($record->status) {
                            'active' => 'Deactivate',
                            'pending' => 'Activate',
                            'expired' => 'Reactivate',
                            'canceled' => 'Reactivate',
                            default => 'Change Status',
                        }
                    )
                    ->icon(
                        fn(UserZipcodeSubscription $record): string =>
                        match ($record->status) {
                            'active' => 'heroicon-o-x-circle',
                            'pending', 'expired', 'canceled' => 'heroicon-o-check-circle',
                            default => 'heroicon-o-arrow-path',
                        }
                    )
                    ->color(
                        fn(UserZipcodeSubscription $record): string =>
                        match ($record->status) {
                            'active' => 'danger',
                            'pending', 'expired', 'canceled' => 'success',
                            default => 'gray',
                        }
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        fn(UserZipcodeSubscription $record): string =>
                        match ($record->status) {
                            'active' => 'Deactivate Subscription',
                            'pending' => 'Activate Subscription',
                            'expired' => 'Reactivate Subscription',
                            'canceled' => 'Reactivate Subscription',
                            default => 'Change Subscription Status',
                        }
                    )
                    ->modalDescription(
                        fn(UserZipcodeSubscription $record): string =>
                        match ($record->status) {
                            'active' => "Are you sure you want to deactivate this subscription?",
                            'pending' => "Are you sure you want to activate this subscription?",
                            'expired' => "Are you sure you want to reactivate this expired subscription?",
                            'canceled' => "Are you sure you want to reactivate this canceled subscription?",
                            default => "Are you sure you want to change the status of this subscription?",
                        }
                    )
                    ->action(function (UserZipcodeSubscription $record) {
                        $newStatus = match ($record->status) {
                            'active' => 'canceled',
                            'pending', 'expired', 'canceled' => 'active',
                            default => 'active',
                        };
                        $record->update(['status' => $newStatus]);
                    })
                    ->visible(
                        fn(UserZipcodeSubscription $record): bool =>
                        in_array($record->status, ['pending', 'active', 'expired', 'canceled'])
                    ),

                Actions\EditAction::make()
                    ->modalHeading('Edit Subscription')
                    ->modalWidth('5xl')
                    ->modalSubmitActionLabel('Save Changes'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Subscription')
                    ->modalDescription('Are you sure you want to delete this subscription? This action cannot be undone.'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Components\Select::make('status')
                                ->label('New Status')
                                ->required()
                                ->options([
                                    'pending' => 'Pending',
                                    'active' => 'Active',
                                    'expired' => 'Expired',
                                    'canceled' => 'Canceled',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (UserZipcodeSubscription $record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Subscriptions')
                        ->modalDescription('Are you sure you want to delete the selected subscriptions? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No subscriptions found')
            ->emptyStateDescription('Get started by creating a new client subscription.')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => Pages\ManageUserZipcodeSubscriptions::route(''),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
