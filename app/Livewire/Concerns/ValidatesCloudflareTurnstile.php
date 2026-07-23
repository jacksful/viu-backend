<?php

namespace App\Livewire\Concerns;

use App\Support\CloudflareTurnstile;
use Illuminate\Validation\ValidationException;

trait ValidatesCloudflareTurnstile
{
    protected function resetCloudflareTurnstile(): void
    {
        $this->dispatch('turnstile-reset');
    }

    protected function validateCloudflareTurnstile(bool $required): void
    {
        if (! $required) {
            return;
        }

        $token = data_get($this->form->getState(), 'turnstile_token');

        if (! CloudflareTurnstile::verify($token, request()->ip())) {
            $this->resetCloudflareTurnstile();

            throw ValidationException::withMessages([
                'data.turnstile_token' => 'Security verification failed. Please try again.',
            ]);
        }
    }
}
