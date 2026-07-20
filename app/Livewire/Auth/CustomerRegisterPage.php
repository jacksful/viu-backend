<?php

namespace App\Livewire\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
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
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;

class CustomerRegisterPage extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;
    use WithRateLimiting;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    protected Width|string|null $maxContentWidth = Width::Large;

    public function mount(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        if (Auth::check() && Auth::user()->role === 'customer') {
            $this->redirectIntended(default: route('user.dashboard'), navigate: false);

            return;
        }

        $this->form->fill();
    }

    public function register(): mixed
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many registration attempts. Please try again in '.$exception->secondsUntilAvailable.' seconds.')
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'password_set_at' => now(),
            'role' => 'customer',
            'status' => 'pending',
        ]);

        event(new Registered($user));

        Auth::login($user);

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');
        }

        return redirect()->route('user.dashboard')
            ->with('status', 'Registration successful!');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('first_name')
                ->label('First name')
                ->required()
                ->maxLength(255)
                ->autofocus(),
            TextInput::make('last_name')
                ->label('Last name')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(User::class, 'email'),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required()
                ->rule(Password::defaults())
                ->autocomplete('new-password'),
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
        return 'Customer Sign Up';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Create your account';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            'Already have an account? '.
            '<a href="'.route('user.login').'" class="fi-link fi-link-size-md fi-color-custom fi-color-primary fi-text-color-600 dark:fi-text-color-400">Sign in</a>'
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('register')
                ->footer([
                    Actions::make([
                        Action::make('register')
                            ->label('Create account')
                            ->color('primary')
                            ->submit('register'),
                    ])->fullWidth(),
                ]),
        ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
