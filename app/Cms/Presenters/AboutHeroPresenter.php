<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class AboutHeroPresenter
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
            'image_url' => MediaUrlResolver::image($this->data['image_path'] ?? null),
            default => $this->data[$name] ?? null,
        };
    }

    /**
     * @return list<string>
     */
    public function titleLines(): array
    {
        $lines = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', (string) ($this->data['title'] ?? '')),
            fn ($line) => $line !== ''
        ));

        if ($lines === []) {
            return ['We put your brand in front of the market', 'before it moves'];
        }

        return $lines;
    }
}
