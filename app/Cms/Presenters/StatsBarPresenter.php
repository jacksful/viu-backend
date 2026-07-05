<?php

namespace App\Cms\Presenters;

class StatsBarPresenter
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

    /**
     * @return list<array{value?: string, label?: string}>
     */
    public function items(): array
    {
        $items = $this->data['items'] ?? [];

        if (! is_array($items) || $items === []) {
            return [
                ['value' => '90', 'label' => 'Pre-market advantage'],
                ['value' => '100%', 'label' => 'Exclusive rights'],
                ['value' => '24/7', 'label' => 'Monitoring'],
            ];
        }

        return $items;
    }
}
