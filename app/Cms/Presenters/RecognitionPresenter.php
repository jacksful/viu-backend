<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class RecognitionPresenter
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
        return match ($name) {
            'right_image_url' => MediaUrlResolver::image(
                $this->data['right_image_path'] ?? null,
                'viu/assets/images/section-3.jpg'
            ),
            'card_logo_url' => MediaUrlResolver::image(
                $this->data['card_logo_path'] ?? null,
                'viu/assets/images/logo-dark.svg'
            ),
            'card_progress_percent' => (int) ($this->data['card_progress_percent'] ?? 0),
            default => $this->data[$name] ?? null,
        };
    }

    public function progressPercentClamped(): int
    {
        return min(100, max(0, (int) ($this->data['card_progress_percent'] ?? 0)));
    }
}
