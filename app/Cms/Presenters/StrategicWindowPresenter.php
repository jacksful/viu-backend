<?php

namespace App\Cms\Presenters;

use App\Cms\Support\MediaUrlResolver;

class StrategicWindowPresenter
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
            'visual_image_url' => MediaUrlResolver::image(
                $this->data['visual_image_path'] ?? null,
                'viu/assets/images/section-1.jpg'
            ),
            'card_icon_url' => MediaUrlResolver::image(
                $this->data['card_icon_path'] ?? null,
                'image/productive-signal.png'
            ),
            'card_metric_percent' => (int) ($this->data['card_metric_percent'] ?? 0),
            default => $this->data[$name] ?? null,
        };
    }

    /**
     * @return list<array{icon_path?: string|null, title?: string, description?: string}>
     */
    public function featureList(): array
    {
        $list = $this->data['features'] ?? [];

        return is_array($list) ? $list : [];
    }

    /**
     * @param  array{icon_path?: string|null}  $feature
     */
    public function featureIconUrl(array $feature, int $index): string
    {
        $bundled = ['image/Container.png', 'image/Container1.png', 'image/Container2.png'];
        $url = MediaUrlResolver::publicUrlFor($feature['icon_path'] ?? null);

        if ($url !== null) {
            return $url;
        }

        return asset($bundled[$index] ?? $bundled[0]);
    }
}
