<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms\Components;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class ManageUsers extends ManageRecords
{
  protected static string $resource = UserResource::class;

  public function getTabs(): array
  {
    return [
      'all' => Tab::make('All')
        ->badge(fn (): int => User::query()->count()),
      'admin' => Tab::make('Admins')
        ->badge(fn (): int => User::query()->where('role', 'admin')->count())
        ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('role', 'admin')),
      'customer' => Tab::make('Customers')
        ->badge(fn (): int => User::query()->where('role', 'customer')->count())
        ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('role', 'customer')),
    ];
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalHeading('Create New User')
        ->modalWidth('5xl')
        ->modalSubmitActionLabel('Create User')
        ->form([
          Grid::make(2)
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
                ->helperText('Auto-generated from first and last name if left empty'),

              Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(\App\Models\User::class, 'email')
                ->maxLength(255)
                ->placeholder('Enter email address'),

              Components\TextInput::make('phone')
                ->label('Phone')
                ->tel()
                ->maxLength(255)
                ->placeholder('Enter phone number'),

              Components\TextInput::make('address')
                ->label('Address')
                ->maxLength(255)
                ->placeholder('Enter street address')
                ->columnSpanFull(),

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
                ->required()
                ->minLength(8)
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->helperText('Minimum 8 characters')
                ->placeholder('Enter password')
                ->columnSpanFull(),

              Components\Checkbox::make('email_verified')
                ->label('Mark email as verified')
                ->default(false)
                ->dehydrated(false)
                ->afterStateUpdated(function ($state, callable $set) {
                  $set('email_verified_at', $state ? now() : null);
                }),

              Components\DateTimePicker::make('email_verified_at')
                ->label('Email Verified At')
                ->hidden()
                ->dehydrated(),
            ]),
        ])
        ->mutateFormDataUsing(function (array $data): array {
          // Auto-generate name if not provided
          if (empty($data['name']) && !empty($data['first_name']) && !empty($data['last_name'])) {
            $data['name'] = "{$data['first_name']} {$data['last_name']}";
          }

          return $data;
        }),
    ];
  }
}
