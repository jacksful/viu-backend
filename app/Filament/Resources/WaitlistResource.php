<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaitlistResource\Pages;
use App\Models\Waitlist;
use App\Services\WaitlistConversionService;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WaitlistResource extends Resource
{
    protected static ?string $model = Waitlist::class;

    protected static ?string $navigationLabel = 'Waitlist';

    protected static ?string $modelLabel = 'Waitlist Entry';

    protected static ?string $pluralModelLabel = 'Waitlist';

    protected static ?string $slug = 'waitlists';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Contact Information')
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->copyable(),

                        Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(30)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('zip_code')
                            ->label('ZIP code')
                            ->maxLength(20)
                            ->disabled()
                            ->dehydrated(),

                        Components\Textarea::make('message')
                            ->label('Message')
                            ->rows(4)
                            ->maxLength(5000)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'read' => 'Read',
                                'contacted' => 'Contacted',
                                'archived' => 'Archived',
                            ])
                            ->default('new')
                            ->placeholder('Select status')
                            ->helperText('Update the status to track waitlist progress'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Submission Details')
                    ->schema([
                        Components\Placeholder::make('submitted_at')
                            ->label('Submitted At')
                            ->content(fn (?Waitlist $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '—'),

                        Components\Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn (?Waitlist $record): string => $record?->updated_at?->format('M j, Y g:i A') ?? '—'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                SchemaComponents\Section::make('Conversion Status')
                    ->schema([
                        Components\Placeholder::make('converted_status')
                            ->label('Converted Status')
                            ->content(fn (?Waitlist $record): string => $record?->convertedToUser
                                ? "Converted to user: {$record->convertedToUser->name} on {$record->converted_at?->format('m/d/Y')}"
                                : 'Not converted'),

                        Components\Placeholder::make('ready_to_convert')
                            ->label('Ready to Convert')
                            ->content(function (?Waitlist $record): string {
                                if (! $record) {
                                    return '—';
                                }

                                if ($record->hasActiveLock()) {
                                    return 'Checkout link already sent. ZIP is locked until '
                                        .($record->locked_until?->format('M j, Y g:i A') ?? '—').'.';
                                }

                                if ($record->isReadyToConvert()) {
                                    return 'Yes — ready to convert and send a checkout link.';
                                }

                                $blockers = $record->conversionBlockers();

                                return $blockers !== []
                                    ? 'Not yet — '.implode(' ', $blockers)
                                    : 'Not yet — entry does not meet all conversion requirements.';
                            })
                            ->visible(fn (?Waitlist $record): bool => $record !== null && $record->canShowConvertAction()),

                        Components\Placeholder::make('checkout_url')
                            ->label('Checkout link')
                            ->content(fn (?Waitlist $record): string => $record?->checkout_url ?: '—')
                            ->visible(fn (?Waitlist $record): bool => filled($record?->checkout_url)),

                        Components\Placeholder::make('locked_until')
                            ->label('ZIP locked until')
                            ->content(fn (?Waitlist $record): string => $record?->locked_until?->format('M j, Y g:i A') ?? '—')
                            ->visible(fn (?Waitlist $record): bool => $record?->locked_until !== null),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (?Waitlist $record): bool => $record !== null),
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

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('zip_code')
                    ->label('ZIP code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(80)
                    ->tooltip(fn (Waitlist $record): string => $record->message ?? '—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'read',
                        'info' => 'contacted',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                Tables\Columns\IconColumn::make('converted_to_user_id')
                    ->label('Converted')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'read' => 'Read',
                        'contacted' => 'Contacted',
                        'archived' => 'Archived',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('converted_to_user_id')
                    ->label('Converted')
                    ->placeholder('All entries')
                    ->trueLabel('Converted only')
                    ->falseLabel('Not converted')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('converted_to_user_id'),
                        false: fn (Builder $query) => $query->whereNull('converted_to_user_id'),
                        blank: fn (Builder $query) => $query,
                    ),

                Tables\Filters\Filter::make('zip_code')
                    ->label('ZIP code')
                    ->form([
                        Components\TextInput::make('zip_code')
                            ->label('ZIP code')
                            ->maxLength(5),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['zip_code'] ?? null),
                            fn (Builder $query): Builder => $query->where('zip_code', $data['zip_code']),
                        );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->label('Submitted Date')
                    ->form([
                        Components\DatePicker::make('created_from')
                            ->label('From'),
                        Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'read' => 'Read',
                                'contacted' => 'Contacted',
                                'archived' => 'Archived',
                            ])
                            ->default(fn (Waitlist $record): string => $record->status)
                            ->placeholder('Select status'),
                    ])
                    ->action(function (Waitlist $record, array $data) {
                        $record->update(['status' => $data['status']]);
                    })
                    ->successNotificationTitle('Status updated successfully'),

                Actions\Action::make('convertToClient')
                    ->label('Convert to Client')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-exclamation-triangle')
                    ->modalIconColor('warning')
                    ->modalHeading(fn (Waitlist $record): string => "Convert '{$record->name}' to a client?")
                    ->modalDescription('Creates their account and sends a checkout link for the ZIP they were waiting on (locks it for 4 days).')
                    ->modalSubmitActionLabel('Convert & send link')
                    ->modalCancelActionLabel('Cancel')
                    ->action(function (Waitlist $record, WaitlistConversionService $conversionService): void {
                        try {
                            $waitlist = $conversionService->convert($record);

                            Notification::make()
                                ->success()
                                ->title('Checkout link sent')
                                ->body("A Stripe checkout link was emailed to {$waitlist->email}. ZIP {$waitlist->zip_code} is locked for 4 days.")
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->danger()
                                ->title('Conversion failed')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    })
                    ->successNotification(null)
                    ->visible(fn (Waitlist $record): bool => $record->canShowConvertAction()),

                Actions\EditAction::make()
                    ->modalHeading('Edit waitlist entry')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Save Changes'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete waitlist entry')
                    ->modalDescription('Are you sure you want to delete this waitlist entry? This action cannot be undone.'),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Components\Select::make('status')
                                ->label('New Status')
                                ->required()
                                ->options([
                                    'new' => 'New',
                                    'read' => 'Read',
                                    'contacted' => 'Contacted',
                                    'archived' => 'Archived',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (Waitlist $record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected')
                        ->modalDescription('Are you sure you want to delete the selected waitlist entries? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No waitlist entries yet')
            ->emptyStateDescription('Submissions from the ZIP availability waitlist form will appear here.')
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWaitlists::route(''),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
