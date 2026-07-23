<?php

namespace App\Filament\Auth;

use App\Forms\Components\Turnstile;
use App\Livewire\Concerns\ValidatesCloudflareTurnstile;
use App\Models\CloudflareSetting;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class AdminLogin extends Login
{
    use ValidatesCloudflareTurnstile;

    public function authenticate(): ?LoginResponse
    {
        if (blank($this->userUndertakingMultiFactorAuthentication)) {
            $this->validateCloudflareTurnstile(CloudflareSetting::singleton()->isRequiredForAdminLogin());
        }

        return parent::authenticate();
    }

    public function form(Schema $schema): Schema
    {
        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        if (CloudflareSetting::singleton()->isRequiredForAdminLogin()) {
            $components[] = Turnstile::make('turnstile_token')
                ->siteKey(fn (): ?string => CloudflareSetting::singleton()->site_key)
                ->label('Security check');
        }

        return $schema->components($components);
    }

    protected function throwFailureValidationException(): never
    {
        $this->resetCloudflareTurnstile();

        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
