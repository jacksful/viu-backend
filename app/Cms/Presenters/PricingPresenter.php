<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class PricingPresenter
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
            'left_image_url' => MediaUrlResolver::image(
                $this->data['left_image_path'] ?? null,
                'viu/assets/images/section-4.jpg'
            ),
            default => $this->data[$name] ?? null,
        };
    }

    /**
     * @return list<non-falsy-string>
     */
    public function featureLines(): array
    {
        $raw = $this->data['feature_lines'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $lines = [];
        foreach ($raw as $row) {
            if (is_array($row)) {
                $t = trim((string) ($row['text'] ?? ''));
                if ($t !== '') {
                    $lines[] = $t;
                }
            } elseif (is_string($row) && trim($row) !== '') {
                $lines[] = trim($row);
            }
        }

        return $lines;
    }
}
