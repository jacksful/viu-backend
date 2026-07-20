<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerSetPasswordPage extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public string $userId;

    public string $hash;

    protected Width|string|null $maxContentWidth = Width::Large;

    public function mount(string $id, string $hash): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->userId = $id;
        $this->hash = $hash;

        $user = $this->resolveVerifiedUser();

        if ($user->password_set_at) {
            session()->flash('status', 'Your password is already set. Please sign in.');
            $this->redirectRoute('user.login', navigate: false);

            return;
        }

        if (session('status')) {
            Notification::make()
                ->title(session('status'))
                ->success()
                ->send();
        }

        $this->form->fill();
    }

    public function storePassword(): mixed
    {
        $user = $this->resolveVerifiedUser();

        if ($user->password_set_at) {
            return redirect()->route('user.login')
                ->with('status', 'Your password is already set. Please sign in.');
        }

        $data = $this->form->getState();

        $user->update([
            'password' => Hash::make($data['password']),
            'password_set_at' => now(),
        ]);

        return redirect()->route('user.login')
            ->with('status', 'Password set successfully! Please sign in with your new password.');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required()
                ->rule(Password::defaults())
                ->autocomplete('new-password')
                ->autofocus(),
            TextInput::make('password_confirmation')
                ->label('Confirm password')
                ->password()
                ->revealable()
                ->required()
                ->same('password')
                ->autocomplete('new-password'),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Set Your Password';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Set your password';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Your email is verified. Create a password to access your account.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('storePassword')
                ->footer([
                    Actions::make([
                        Action::make('storePassword')
                            ->label('Set password')
                            ->color('primary')
                            ->submit('storePassword'),
                    ])->fullWidth(),
                ]),
        ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function resolveVerifiedUser(): User
    {
        $user = User::findOrFail($this->userId);

        if ($user->role !== 'customer') {
            abort(403);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $this->hash)) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Please verify your email address first.');
        }

        return $user;
    }
}
