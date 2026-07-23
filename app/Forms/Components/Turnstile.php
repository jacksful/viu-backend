<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class Turnstile extends Field
{
    /**
     * @var view-string
     */
    protected string $view = 'forms.components.turnstile';

    protected string | Closure | null $siteKey = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(true);
        $this->required(fn (Turnstile $component): bool => filled($component->getSiteKey()));
        $this->rule(fn (Turnstile $component): string => filled($component->getSiteKey()) ? 'required' : 'nullable');
    }

    public function siteKey(string | Closure | null $siteKey): static
    {
        $this->siteKey = $siteKey;

        return $this;
    }

    public function getSiteKey(): ?string
    {
        return $this->evaluate($this->siteKey);
    }
}
