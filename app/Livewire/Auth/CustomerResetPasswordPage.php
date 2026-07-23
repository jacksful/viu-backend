<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class CustomerResetPasswordPage extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public string $token;

    protected Width|string|null $maxContentWidth = Width::Large;

    public function mount(string $token): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->token = $token;

        $this->form->fill([
            'email' => request()->query('email', ''),
        ]);
    }

    public function resetPassword(): mixed
    {
        $data = $this->form->getState();

        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $this->token,
            ],
            function (User $user, string $password): void {
                if ($user->role !== 'customer') {
                    throw ValidationException::withMessages([
                        'data.email' => 'This password reset link is not valid.',
                    ]);
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_set_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('user.login')
                ->with('status', 'Your password has been reset. Please sign in with your new password.');
        }

        throw ValidationException::withMessages([
            'data.email' => __($status),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->autocomplete('email')
                ->autofocus(),
            TextInput::make('password')
                ->label('New password')
                ->password()
                ->revealable()
                ->required()
                ->rule(PasswordRule::defaults())
                ->autocomplete('new-password'),
            TextInput::make('password_confirmation')
                ->label('Confirm new password')
                ->password()
                ->revealable()
                ->required()
                ->same('password')
                ->autocomplete('new-password'),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Reset Password';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Choose a new password';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            'Enter your new password below. '.
            '<a href="'.route('user.login').'" class="fi-link fi-link-size-md fi-color-custom fi-color-primary fi-text-color-600 dark:fi-text-color-400">Back to sign in</a>'
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('resetPassword')
                ->footer([
                    Actions::make([
                        Action::make('resetPassword')
                            ->label('Reset password')
                            ->color('primary')
                            ->submit('resetPassword'),
                    ])->fullWidth(),
                ]),
        ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
