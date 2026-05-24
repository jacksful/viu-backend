<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\User;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationLabel = 'Lead Management';

    protected static ?int $navigationSort = 12;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    // public static function getNavigationGroup(): ?string
    // {
    //     return 'Customer Management';
    // }

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
                            ->placeholder('Enter full name'),

                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter email address')
                            ->unique(ignoreRecord: true),

                        Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Enter phone number')
                            ->mask('(999) 999-9999'),

                        Components\Placeholder::make('submitted_date')
                            ->label('Submitted Date')
                            ->content(fn (?Lead $record): string => $record?->created_at?->format('m/d/Y') ?? now()->format('m/d/Y'))
                            ->visible(fn (?Lead $record): bool => $record !== null),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('ZIP Code Selection')
                    ->schema([
                        Components\Select::make('zipcodes')
                            ->label('Selected ZIP Codes')
                            ->relationship('zipcodes', 'code')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn (Zipcode $record): string => "{$record->code} - {$record->city}, {$record->state}")
                            ->helperText('Select one or more ZIP codes this lead is interested in'),
                    ])
                    ->collapsible(),

                SchemaComponents\Section::make('Initial Notes')
                    ->schema([
                        Components\Textarea::make('initial_notes')
                            ->label('Initial Notes')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Enter initial notes or comments from the lead'),
                    ])
                    ->collapsible(),

                SchemaComponents\Section::make('Status and Dates')
                    ->schema([
                        Components\Select::make('lead_status')
                            ->label('Lead Status')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'interested' => 'Interested',
                                'contacted' => 'Contacted',
                                'not_interested' => 'Not Interested',
                            ])
                            ->default('new')
                            ->placeholder('Select status'),

                        Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->required()
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                            ])
                            ->default('unpaid')
                            ->placeholder('Select payment status'),

                        Components\DatePicker::make('last_contact_date')
                            ->label('Last Contact Date')
                            ->displayFormat('m/d/Y')
                            ->native(false)
                            ->placeholder('Select date'),

                        Components\DatePicker::make('next_follow_up_date')
                            ->label('Next Follow-up Date')
                            ->displayFormat('m/d/Y')
                            ->native(false)
                            ->placeholder('Select date'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Internal Comments / Call Notes')
                    ->schema([
                        Components\Textarea::make('internal_comments')
                            ->label('Internal Comments / Call Notes')
                            ->rows(4)
                            ->maxLength(2000)
                            ->placeholder('Enter internal comments or call notes'),
                    ])
                    ->collapsible(),

                SchemaComponents\Section::make('Conversion Status')
                    ->schema([
                        Components\Placeholder::make('converted_status')
                            ->label('Converted Status')
                            ->content(fn (?Lead $record): string => $record?->convertedToUser
                                ? "Converted to user: {$record->convertedToUser->name} on {$record->converted_at?->format('m/d/Y')}"
                                : 'Not converted'),

                        Components\Placeholder::make('ready_to_convert')
                            ->label('Ready to Convert')
                            ->content(fn (?Lead $record): string => $record && $record->isReadyToConvert()
                                ? 'Yes - This lead meets all requirements and can be converted to a client'
                                : 'No - Lead does not meet all conversion requirements')
                            ->visible(fn (?Lead $record): bool => $record !== null && ! $record->converted_to_user_id),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (?Lead $record): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (Lead $record): string => $record->phone
                        ? "{$record->name} ({$record->phone})"
                        : $record->name),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('zipcodes.code')
                    ->label('ZIP Codes')
                    ->badge()
                    ->color('info')
                    ->separator(',')
                    ->limit(3)
                    ->tooltip(fn (Lead $record): string => $record->zipcodes->pluck('code')->join(', ')),

                Tables\Columns\TextColumn::make('lead_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'info' => 'new',
                        'success' => 'interested',
                        'warning' => 'contacted',
                        'gray' => 'not_interested',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'unpaid',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_follow_up_date')
                    ->label('Next Follow-up')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—')
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('lead_status')
                    ->label('Status')
                    ->options([
                        'new' => 'New',
                        'interested' => 'Interested',
                        'contacted' => 'Contacted',
                        'not_interested' => 'Not Interested',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('converted_to_user_id')
                    ->label('Converted')
                    ->placeholder('All leads')
                    ->trueLabel('Converted only')
                    ->falseLabel('Not converted')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('converted_to_user_id'),
                        false: fn (Builder $query) => $query->whereNull('converted_to_user_id'),
                        blank: fn (Builder $query) => $query,
                    ),

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
                Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Lead $record): string => "Lead Details - {$record->name}")
                    ->modalSubheading('Review and update lead information.')
                    ->modalWidth('5xl')
                    ->form([
                        SchemaComponents\Section::make('Contact Information')
                            ->schema([
                                Components\Placeholder::make('name')
                                    ->label('Name')
                                    ->content(fn (Lead $record): string => $record->name),

                                Components\Placeholder::make('email')
                                    ->label('Email')
                                    ->content(fn (Lead $record): string => $record->email),

                                Components\Placeholder::make('phone')
                                    ->label('Phone')
                                    ->content(fn (Lead $record): string => $record->phone ?? '—'),

                                Components\Placeholder::make('submitted_date')
                                    ->label('Submitted Date')
                                    ->content(fn (Lead $record): string => $record->created_at->format('m/d/Y')),

                                Components\Placeholder::make('zipcodes')
                                    ->label('Selected ZIP Codes')
                                    ->content(fn (Lead $record): string => $record->zipcodes->pluck('code')->map(fn ($code) => "ZIP {$code}")->join(', ') ?: '—'),
                            ])
                            ->columns(2),

                        SchemaComponents\Section::make('Initial Notes')
                            ->schema([
                                Components\Placeholder::make('initial_notes')
                                    ->label('Initial Notes')
                                    ->content(fn (Lead $record): string => $record->initial_notes ?? '—'),
                            ]),

                        SchemaComponents\Section::make('Status and Dates')
                            ->schema([
                                Components\Select::make('lead_status')
                                    ->label('Lead Status')
                                    ->options([
                                        'new' => 'New',
                                        'interested' => 'Interested',
                                        'contacted' => 'Contacted',
                                        'not_interested' => 'Not Interested',
                                    ])
                                    ->default(fn (Lead $record): string => $record->lead_status),

                                Components\Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->options([
                                        'paid' => 'Paid',
                                        'unpaid' => 'Unpaid',
                                    ])
                                    ->default(fn (Lead $record): string => $record->payment_status),

                                Components\DatePicker::make('last_contact_date')
                                    ->label('Last Contact Date')
                                    ->displayFormat('m/d/Y')
                                    ->native(false)
                                    ->default(fn (Lead $record) => $record->last_contact_date),

                                Components\DatePicker::make('next_follow_up_date')
                                    ->label('Next Follow-up Date')
                                    ->displayFormat('m/d/Y')
                                    ->native(false)
                                    ->default(fn (Lead $record) => $record->next_follow_up_date),
                            ])
                            ->columns(2),

                        SchemaComponents\Section::make('Internal Comments / Call Notes')
                            ->schema([
                                Components\Textarea::make('internal_comments')
                                    ->label('Internal Comments / Call Notes')
                                    ->rows(4)
                                    ->default(fn (Lead $record) => $record->internal_comments),
                            ]),

                        SchemaComponents\Section::make('Ready to Convert')
                            ->schema([
                                Components\Placeholder::make('ready_status')
                                    ->label('')
                                    ->content(fn (Lead $record): string => $record->isReadyToConvert()
                                        ? 'This lead meets all requirements and can be converted to a client'
                                        : 'This lead does not meet all conversion requirements')
                                    ->extraAttributes(fn (Lead $record): array => [
                                        'class' => $record->isReadyToConvert()
                                            ? 'bg-green-50 border border-green-200 rounded-lg p-4 text-green-800'
                                            : 'bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-600',
                                    ]),
                            ])
                            ->visible(fn (Lead $record): bool => ! $record->converted_to_user_id),
                    ])
                    ->modalSubmitAction(fn (Actions\Action $action) => $action->label('Update Lead'))
                    ->action(function (Lead $record, array $data) {
                        $record->update($data);
                    })
                    ->successNotificationTitle('Lead updated successfully'),

                Actions\Action::make('convertToClient')
                    ->label('Convert to Client')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Convert Lead to Client')
                    ->modalDescription(fn (Lead $record): string => "Are you sure you want to convert {$record->name} to a client? This will create a new user account.")
                    ->modalWidth('2xl')
                    ->form([
                        Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Lead $record): string => explode(' ', $record->name)[0] ?? ''),

                        Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn (Lead $record): string => implode(' ', array_slice(explode(' ', $record->name), 1))),

                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->default(fn (Lead $record): string => $record->email)
                            ->unique(User::class, 'email', ignorable: null, ignoreRecord: false),

                        Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->default(fn (Lead $record): string => $record->phone),

                        Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->helperText('Minimum 8 characters. User will need to reset password if not provided.')
                            ->default(fn () => Str::random(16)),

                        Components\Checkbox::make('send_verification_email')
                            ->label('Send email verification')
                            ->default(true)
                            ->helperText('Send verification email to the new user'),
                    ])
                    ->action(function (Lead $record, array $data) {
                        // Create user
                        $user = User::create([
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'name' => "{$data['first_name']} {$data['last_name']}",
                            'email' => $data['email'],
                            'phone' => $data['phone'] ?? null,
                            'password' => Hash::make($data['password'] ?? Str::random(16)),
                            'role' => 'customer',
                            'status' => 'active',
                            'email_verified_at' => $data['send_verification_email'] ? null : now(),
                        ]);

                        // One subscription row per user: all lead ZIPs in zipcode_ids (see UserZipcodeSubscription schema).
                        if ($record->zipcodes->isNotEmpty()) {
                            UserZipcodeSubscription::create([
                                'user_id' => $user->id,
                                'zipcode_ids' => $record->zipcodes->pluck('id')->all(),
                                'start_date' => now()->toDateString(),
                                'end_date' => null,
                                'status' => 'active',
                            ]);
                        }

                        // Update lead
                        $record->update([
                            'converted_to_user_id' => $user->id,
                            'converted_at' => now(),
                        ]);

                        // Send verification email if requested
                        if ($data['send_verification_email'] && ! $user->email_verified_at) {
                            $user->sendEmailVerificationNotification();
                        }
                    })
                    ->successNotificationTitle('Lead converted to client successfully')
                    ->visible(fn (Lead $record): bool => ! $record->converted_to_user_id),

                Actions\EditAction::make()
                    ->modalHeading('Edit Lead')
                    ->modalWidth('5xl')
                    ->modalSubmitActionLabel('Update Lead'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Lead')
                    ->modalDescription('Are you sure you want to delete this lead? This action cannot be undone.'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Components\Select::make('lead_status')
                                ->label('New Status')
                                ->required()
                                ->options([
                                    'new' => 'New',
                                    'interested' => 'Interested',
                                    'contacted' => 'Contacted',
                                    'not_interested' => 'Not Interested',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (Lead $record) use ($data) {
                                $record->update(['lead_status' => $data['lead_status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Leads')
                        ->modalDescription('Are you sure you want to delete the selected leads? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No leads found')
            ->emptyStateDescription('Leads from the landing page will appear here.')
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
            'index' => Pages\ManageLeads::route(''),
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
