<?php

namespace App\Cms\Presenters;

class AboutPrinciplesPresenter
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

    /**
     * @return list<array{title?: string, description?: string}>
     */
    public function principleList(): array
    {
        $list = $this->data['principles'] ?? [];

        return is_array($list) ? $list : [];
    }
}
