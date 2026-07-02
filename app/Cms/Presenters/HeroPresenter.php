<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class HeroPresenter
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
            'image_url' => MediaUrlResolver::image(
                $this->data['image_path'] ?? null,
                'viu/assets/images/hero-bg.jpg'
            ),
            default => $this->data[$name] ?? null,
        };
    }
}
