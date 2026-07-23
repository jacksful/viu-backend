<?php

namespace App\Livewire\Auth;

use App\Forms\Components\Turnstile;
use App\Livewire\Concerns\ValidatesCloudflareTurnstile;
use App\Models\CloudflareSetting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class CustomerLoginPage extends SimplePage
{
    use RestrictsFileUploadsToSchemaComponents;
    use ValidatesCloudflareTurnstile;
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

        if (session('status')) {
            Notification::make()
                ->title(session('status'))
                ->success()
                ->send();
        }

        $this->form->fill();
    }

    public function authenticate(): mixed
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many login attempts. Please try again in '.$exception->secondsUntilAvailable.' seconds.')
                ->danger()
                ->send();

            return null;
        }

        $this->validateCloudflareTurnstile(CloudflareSetting::singleton()->isRequiredForCustomerLogin());

        $data = $this->form->getState();

        if (! Auth::attempt(
            ['email' => $data['email'], 'password' => $data['password']],
            $data['remember'] ?? false,
        )) {
            $this->resetCloudflareTurnstile();

            throw ValidationException::withMessages([
                'data.email' => 'The provided credentials do not match our records.',
            ]);
        }

        request()->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== 'customer') {
            Auth::logout();

            $this->resetCloudflareTurnstile();

            throw ValidationException::withMessages([
                'data.email' => 'You do not have access to the customer portal.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('status', 'Please verify your email address to access the dashboard.');
        }

        if ($user->status === 'pending' && $user->hasVerifiedEmail()) {
            $user->update(['status' => 'active']);
        }

        return redirect()->intended(route('user.dashboard'));
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
                ->label('Password')
                ->password()
                ->revealable()
                ->autocomplete('current-password')
                ->required()
                ->hint(new HtmlString(
                    '<a href="'.route('user.password.request').'" class="fi-link fi-link-size-sm fi-color-custom fi-color-primary fi-text-color-600 dark:fi-text-color-400">Forgot password?</a>'
                )),
            Checkbox::make('remember')
                ->label('Remember me'),
            ...$this->getTurnstileFormComponents(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Customer Login';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Sign in';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            'Don\'t have an account? '.
            '<a href="'.route('user.register').'" class="fi-link fi-link-size-md fi-color-custom fi-color-primary fi-text-color-600 dark:fi-text-color-400">Sign up</a>'
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('authenticate')
                ->footer([
                    Actions::make([
                        Action::make('authenticate')
                            ->label('Sign in')
                            ->color('primary')
                            ->submit('authenticate'),
                    ])->fullWidth(),
                ]),
        ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @return array<int, Turnstile>
     */
    protected function getTurnstileFormComponents(): array
    {
        if (! CloudflareSetting::singleton()->isRequiredForCustomerLogin()) {
            return [];
        }

        return [
            Turnstile::make('turnstile_token')
                ->siteKey(fn (): ?string => CloudflareSetting::singleton()->site_key)
                ->label('Security check'),
        ];
    }
}
