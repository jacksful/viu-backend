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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\HtmlString;

class CustomerForgotPasswordPage extends SimplePage
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

        if (session('status')) {
            Notification::make()
                ->title(session('status'))
                ->success()
                ->send();
        }

        $this->form->fill();
    }

    public function sendResetLink(): mixed
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many attempts. Please try again in '.$exception->secondsUntilAvailable.' seconds.')
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if ($user?->role === 'customer') {
            Password::sendResetLink(['email' => $data['email']]);
        }

        return redirect()->route('user.login')
            ->with('status', 'If an account exists with that email, we sent a password reset link.');
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
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Forgot Password';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Forgot your password?';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            'Enter your email and we\'ll send you a link to reset your password. '.
            '<a href="'.route('user.login').'" class="fi-link fi-link-size-md fi-color-custom fi-color-primary fi-text-color-600 dark:fi-text-color-400">Back to sign in</a>'
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('sendResetLink')
                ->footer([
                    Actions::make([
                        Action::make('sendResetLink')
                            ->label('Send reset link')
                            ->color('primary')
                            ->submit('sendResetLink'),
                    ])->fullWidth(),
                ]),
        ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
