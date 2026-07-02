<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class FaqPresenter
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
            'support_icon_url' => MediaUrlResolver::image(
                $this->data['support_icon_path'] ?? null,
                'image/email-ico.png'
            ),
            default => $this->data[$name] ?? null,
        };
    }

    /**
     * @return list<array{question?: string, answer?: string, opened?: bool}>
     */
    public function faqList(): array
    {
        $list = $this->data['faq_items'] ?? [];

        return is_array($list) ? $list : [];
    }

    public function defaultOpenFaqIndex(): int
    {
        foreach ($this->faqList() as $index => $row) {
            if (! empty($row['opened'])) {
                return $index;
            }
        }

        return 0;
    }
}
