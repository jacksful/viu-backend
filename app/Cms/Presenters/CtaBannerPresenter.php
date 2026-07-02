<?php

namespace App\Cms\Presenters;

class CtaBannerPresenter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private array $data) {}

    /**
     * @param  array<string, mixed>  $content
     */
    public static function from(array $content): self
    {
        return new self($content);
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? match ($name) {
            'badge_text' => 'Secure your territory',
            'title' => "The best time to be known is before you're needed",
            'text' => 'Claim your exclusive ZIP before a competitor locks it in, one agent per market, positioned long before search begins.',
            'primary_button_label' => 'Check territory',
            'secondary_button_label' => 'Contact a specialist',
            default => null,
        };
    }
}
