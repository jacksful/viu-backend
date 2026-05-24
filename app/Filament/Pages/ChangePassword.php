<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page implements HasForms
{
  use InteractsWithForms;

  protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

  protected string $view = 'filament.pages.change-password';

  protected static bool $shouldRegisterNavigation = false;

  protected static ?string $routePath = 'change-password';

  public ?array $data = [];

  public function mount(): void
  {
    $this->form->fill();
  }

  public function form(Schema $schema): Schema
  {
    return $schema
      ->schema([
        SchemaComponents\Section::make('Change Password')
          ->description('Update your account password. Make sure to use a strong password.')
          ->schema([
            TextInput::make('current_password')
              ->label('Current Password')
              ->password()
              ->required()
              ->currentPassword()
              ->revealable()
              ->autocomplete('current-password'),

            TextInput::make('password')
              ->label('New Password')
              ->password()
              ->required()
              ->rules([
                Password::min(8)
                  ->letters()
                  ->mixedCase()
                  ->numbers()
                  ->symbols(),
              ])
              ->revealable()
              ->autocomplete('new-password')
              ->confirmed(),

            TextInput::make('password_confirmation')
              ->label('Confirm New Password')
              ->password()
              ->required()
              ->revealable()
              ->autocomplete('new-password'),
          ])
          ->columns(1),
      ])
      ->statePath('data');
  }

  public function save(): void
  {
    $data = $this->form->getState();

    $user = Auth::user();

    if (!Hash::check($data['current_password'], $user->password)) {
      Notification::make()
        ->title('Error')
        ->body('Current password is incorrect.')
        ->danger()
        ->send();

      return;
    }

    $user->update([
      'password' => Hash::make($data['password']),
    ]);

    Notification::make()
      ->title('Success')
      ->body('Password has been updated successfully.')
      ->success()
      ->send();

    $this->form->fill();
  }

  public function getTitle(): string
  {
    return 'Change Password';
  }

  public function getHeading(): string
  {
    return 'Change Password';
  }
}
