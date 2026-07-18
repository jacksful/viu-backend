<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationLabel = 'Feedback';

    protected static ?int $navigationSort = 2;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Website';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Feedback Information')
                    ->schema([
                        Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('The user who submitted this feedback'),

                        Components\TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->rows(6)
                            ->maxLength(5000)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'reviewed' => 'Reviewed',
                                'resolved' => 'Resolved',
                            ])
                            ->default('pending')
                            ->placeholder('Select status')
                            ->helperText('Update the status to track feedback progress'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('User Information')
                    ->schema([
                        Components\Placeholder::make('user_email')
                            ->label('User Email')
                            ->content(fn(?Feedback $record): string => $record?->user?->email ?? '—'),

                        Components\Placeholder::make('user_phone')
                            ->label('User Phone')
                            ->content(fn(?Feedback $record): string => $record?->user?->phone ?? '—'),

                        Components\Placeholder::make('submitted_at')
                            ->label('Submitted At')
                            ->content(fn(?Feedback $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '—'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
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
                    ->label('User')
                    ->searchable(['user.name', 'user.email'])
                    ->sortable()
                    ->formatStateUsing(fn(Feedback $record): string => $record->user?->name ?? 'Unknown User')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn(Feedback $record): string => $record->subject)
                    ->wrap(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(100)
                    ->tooltip(fn(Feedback $record): string => $record->message)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'reviewed',
                        'success' => 'resolved',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable()
                    ->icon(fn(string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'reviewed' => 'heroicon-o-eye',
                        'resolved' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),

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
                        'reviewed' => 'Reviewed',
                        'resolved' => 'Resolved',
                    ])
                    ->multiple(),

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
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn(Feedback $record): string => "Feedback from {$record->user?->name}")
                    ->modalWidth('2xl')
                    ->modalContent(fn(Feedback $record) => view('filament.resources.feedback.view-modal', [
                        'feedback' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'reviewed' => 'Reviewed',
                                'resolved' => 'Resolved',
                            ])
                            ->default(fn(Feedback $record): string => $record->status)
                            ->placeholder('Select status'),
                    ])
                    ->action(function (Feedback $record, array $data) {
                        $record->update(['status' => $data['status']]);
                    })
                    ->successNotificationTitle('Status updated successfully'),

                Actions\EditAction::make()
                    ->modalHeading('Edit Feedback Status')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Save Changes'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Feedback')
                    ->modalDescription('Are you sure you want to delete this feedback? This action cannot be undone.'),
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
                                    'pending' => 'Pending',
                                    'reviewed' => 'Reviewed',
                                    'resolved' => 'Resolved',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (Feedback $record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Feedback')
                        ->modalDescription('Are you sure you want to delete the selected feedback? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No feedback found')
            ->emptyStateDescription('Customer feedback will appear here once submitted.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
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
            'index' => Pages\ManageFeedback::route(''),
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
