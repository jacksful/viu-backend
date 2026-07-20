<?php

namespace App\Auth;

use Closure;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use SensitiveParameter;

class AdminEmailAuthentication extends EmailAuthentication
{
    /**
     * @param  Authenticatable&HasEmailAuthentication  $user
     * @return array<int, TextInput>
     */
    public function getChallengeFormComponents(Authenticatable $user): array
    {
        return [
            TextInput::make('code')
                ->label('Verification code')
                ->validationAttribute('code')
                ->placeholder('Enter 6-digit code')
                ->numeric()
                ->length(6)
                ->autocomplete('one-time-code')
                ->autofocus()
                ->extraInputAttributes(['class' => 'text-center'])
                ->belowContent(Action::make('resend')
                    ->label('Resend code')
                    ->link()
                    ->action(function () use ($user): void {
                        if (! $this->sendCode($user)) {
                            Notification::make()
                                ->title('Too many resend attempts. Please wait before trying again.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('A new verification code has been sent to your email.')
                            ->success()
                            ->send();
                    }))
                ->required()
                ->rule(function (): Closure {
                    return function (string $attribute, #[SensitiveParameter] $value, Closure $fail): void {
                        if ($this->verifyCode($value)) {
                            return;
                        }

                        $fail('The verification code is invalid or has expired.');
                    };
                }),
        ];
    }
}
