<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Users';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 6;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Personal Information')
                    ->schema([
                        Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter first name'),

                        Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter last name'),

                        Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter full name')
                            ->helperText('This will be auto-generated from first and last name if left empty'),

                        Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Enter email address'),

                        Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Enter phone number'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Address Information')
                    ->schema([
                        Components\TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->placeholder('Enter street address'),

                        Components\TextInput::make('city')
                            ->label('City')
                            ->maxLength(255)
                            ->placeholder('Enter city'),

                        Components\TextInput::make('state')
                            ->label('State')
                            ->maxLength(255)
                            ->placeholder('Enter state'),

                        Components\TextInput::make('zip')
                            ->label('ZIP Code')
                            ->maxLength(255)
                            ->placeholder('Enter ZIP code'),

                        Components\TextInput::make('country')
                            ->label('Country')
                            ->maxLength(255)
                            ->default('USA')
                            ->placeholder('Enter country'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                SchemaComponents\Section::make('Account Settings')
                    ->schema([
                        Components\Select::make('role')
                            ->label('Role')
                            ->required()
                            ->options([
                                'admin' => 'Admin',
                                'customer' => 'Customer',
                            ])
                            ->default('customer')
                            ->placeholder('Select role'),

                        Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'pending' => 'Pending',
                                'blocked' => 'Blocked',
                            ])
                            ->default('active')
                            ->placeholder('Select status'),

                        Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->minLength(8)
                            ->helperText('Leave empty to keep current password when editing')
                            ->placeholder('Enter password'),

                        Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->displayFormat('Y-m-d H:i')
                            ->placeholder('Select date and time'),
                    ])
                    ->columns(2),
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
                    ->searchable(['first_name', 'last_name', 'name'])
                    ->sortable()
                    ->formatStateUsing(fn(User $record): string => $record->name ?? "{$record->first_name} {$record->last_name}"),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->colors([
                        'success' => 'admin',
                        'info' => 'customer',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'blocked',
                        'gray' => 'inactive',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
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
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                        'blocked' => 'Blocked',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->placeholder('All users')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn(Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('toggleStatus')
                    ->label(fn(User $record): string => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn(User $record): string => $record->status === 'active' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(User $record): string => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn(User $record): string => $record->status === 'active' ? 'Deactivate User' : 'Activate User')
                    ->modalDescription(fn(User $record): string => $record->status === 'active'
                        ? "Are you sure you want to deactivate {$record->name}? They will not be able to access the system."
                        : "Are you sure you want to activate {$record->name}? They will be able to access the system.")
                    ->action(function (User $record) {
                        $record->update([
                            'status' => $record->status === 'active' ? 'inactive' : 'active',
                        ]);
                    })
                    ->visible(fn(User $record): bool => Auth::user()->id !== $record->id),

                Actions\EditAction::make()
                    ->modalHeading('Edit User')
                    ->modalWidth('5xl')
                    ->modalSubmitActionLabel('Save Changes'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete User')
                    ->modalDescription('Are you sure you want to delete this user? This action cannot be undone.')
                    ->visible(fn(User $record): bool => Auth::user()->id !== $record->id),
                ]),
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
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'pending' => 'Pending',
                                    'blocked' => 'Blocked',
                                ])
                                ->placeholder('Select status'),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function (User $record) use ($data) {
                                // Prevent admin from changing their own status
                                if ($record->id !== Auth::user()->id) {
                                    $record->update(['status' => $data['status']]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Users')
                        ->modalDescription('Are you sure you want to delete the selected users? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Get started by creating a new user.')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ManageUsers::route(''),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}
