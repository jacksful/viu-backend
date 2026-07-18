<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationLabel = 'Inquiries';

    protected static ?string $modelLabel = 'Inquiry';

    protected static ?string $pluralModelLabel = 'Inquiries';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-queue-list';
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

                        Components\TextInput::make('zip_of_interest')
                            ->label('ZIP code of interest')
                            ->maxLength(20)
                            ->disabled()
                            ->dehydrated(),

                        Components\TextInput::make('subject')
                            ->label('Subject')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Components\Textarea::make('message')
                            ->label('How can we help you?')
                            ->rows(6)
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
                                'replied' => 'Replied',
                                'archived' => 'Archived',
                            ])
                            ->default('new')
                            ->placeholder('Select status')
                            ->helperText('Update the status to track contact progress'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Submission Details')
                    ->schema([
                        Components\Placeholder::make('submitted_at')
                            ->label('Submitted At')
                            ->content(fn(?Contact $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '—'),

                        Components\Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn(?Contact $record): string => $record?->updated_at?->format('M j, Y g:i A') ?? '—'),
                    ])
                    ->columns(2)
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

                Tables\Columns\TextColumn::make('zip_of_interest')
                    ->label('ZIP of interest')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn(Contact $record): string => $record->subject ?? '—')
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(100)
                    ->tooltip(fn(Contact $record): string => $record->message !== '' ? $record->message : '—')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'read',
                        'info' => 'replied',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable()
                    ->icon(fn(string $state): string => match ($state) {
                        'new' => 'heroicon-o-bell',
                        'read' => 'heroicon-o-eye',
                        'replied' => 'heroicon-o-check-circle',
                        'archived' => 'heroicon-o-archive-box',
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
                        'new' => 'New',
                        'read' => 'Read',
                        'replied' => 'Replied',
                        'archived' => 'Archived',
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
                    ->modalHeading(fn(Contact $record): string => "Inquiry from {$record->name}")
                    ->modalWidth('2xl')
                    ->modalContent(fn(Contact $record) => view('filament.resources.contact.view-modal', [
                        'contact' => $record,
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
                                'new' => 'New',
                                'read' => 'Read',
                                'replied' => 'Replied',
                                'archived' => 'Archived',
                            ])
                            ->default(fn(Contact $record): string => $record->status)
                            ->placeholder('Select status'),
                    ])
                    ->action(function (Contact $record, array $data) {
                        $record->update(['status' => $data['status']]);
                    })
                    ->successNotificationTitle('Status updated successfully'),

                Actions\EditAction::make()
                    ->modalHeading('Edit status')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Save Changes'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete submission')
                    ->modalDescription('Are you sure you want to delete this submission? This action cannot be undone.'),
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
                                    'replied' => 'Replied',
                                    'archived' => 'Archived',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (Contact $record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected')
                        ->modalDescription('Are you sure you want to delete the selected submissions? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No submissions yet')
            ->emptyStateDescription('Submissions from your site forms will appear here once received.')
            ->emptyStateIcon('heroicon-o-queue-list');
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
            'index' => Pages\ManageContacts::route(''),
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
