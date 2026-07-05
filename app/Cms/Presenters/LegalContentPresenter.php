<?php

namespace App\Cms\Presenters;

class LegalContentPresenter
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
        return $this->data[$name] ?? null;
    }

    public function bodyHtml(): string
    {
        return (string) ($this->data['body'] ?? '');
    }
}
