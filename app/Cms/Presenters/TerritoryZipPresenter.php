<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class TerritoryZipPresenter
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
            'left_visual_image_url' => MediaUrlResolver::image(
                $this->data['left_visual_image_path'] ?? null,
                'viu/assets/images/section-2.jpg'
            ),
            'left_card_icon_url' => MediaUrlResolver::image(
                $this->data['left_card_icon_path'] ?? null,
                'image/ZIP-Territory-ico.png'
            ),
            'quote_icon_url' => MediaUrlResolver::image(
                $this->data['quote_icon_path'] ?? null,
                'image/territory-ico4.png'
            ),
            'checklist_check_icon_url' => MediaUrlResolver::image(
                $this->data['checklist_check_icon_path'] ?? null,
                'image/check-box.png'
            ),
            default => $this->data[$name] ?? null,
        };
    }

    /**
     * @return list<non-falsy-string>
     */
    public function checklistLines(): array
    {
        $raw = $this->data['checklist_items'] ?? [];
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

    /**
     * @return list<array{icon_path?: string|null, label?: string}>
     */
    public function featureList(): array
    {
        $list = $this->data['feature_columns'] ?? [];

        return is_array($list) ? $list : [];
    }

    /**
     * @param  array{icon_path?: string|null, label?: string}  $feature
     */
    public function featureIconUrl(array $feature, int $index): string
    {
        $bundled = [
            'image/territory-ico1.png',
            'image/territory-ico2.png',
            'image/territory-ico3.png',
        ];
        $url = MediaUrlResolver::publicUrlFor($feature['icon_path'] ?? null);

        if ($url !== null) {
            return $url;
        }

        return asset($bundled[$index] ?? $bundled[0]);
    }
}
