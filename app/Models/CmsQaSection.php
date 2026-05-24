<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsQaSection extends Model
{
    protected $fillable = [
        'badge_text',
        'heading',
        'intro',
        'support_label',
        'support_email',
        'support_icon_path',
        'faq_items',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'faq_items' => 'array',
        ];
    }

    protected $appends = [
        'support_icon_url',
    ];

    public static function singleton(): self
    {
        $defaults = [
            'badge_text' => 'Information center',
            'heading' => 'Common inquiries',
            'intro' => 'Everything you need to know about territory ownership and our predictive visibility network.',
            'support_label' => 'Email support',
            'support_email' => 'support@viu.com',
            'support_icon_path' => null,
            'faq_items' => [
                [
                    'question' => 'How early does VIU reach homeowners?',
                    'answer' => 'Everything you need to know about territory ownership, market timing, and our structural control model.',
                    'opened' => true,
                ],
                [
                    'question' => 'How often does my brand appear?',
                    'answer' => 'Frequency depends on your ZIP coverage and campaign settings. Your brand surfaces in predictive touchpoints where VIU has visibility for your subscribed territories.',
                    'opened' => false,
                ],
                [
                    'question' => 'How many agents can use the same ZIP?',
                    'answer' => 'Each ZIP is reserved for one subscriber at a time so you are not competing with another VIU subscriber in the same area. Team seats are configured under your account.',
                    'opened' => false,
                ],
                [
                    'question' => 'What happens if I cancel?',
                    'answer' => 'You retain access through the end of your billing period. After that, ZIP coverage releases and predictive signals for those territories are no longer included in your plan.',
                    'opened' => false,
                ],
            ],
        ];

        return static::query()->firstOrCreate([], $defaults);
    }

    public static function publicUrlFor(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'cms/')) {
            return asset('storage/'.$path);
        }

        return asset($path);
    }

    public function getSupportIconUrlAttribute(): string
    {
        $url = static::publicUrlFor($this->support_icon_path);

        return $url ?? asset('image/email-ico.png');
    }

    /**
     * @return list<array{question?: string, answer?: string, opened?: bool}>
     */
    public function faqList(): array
    {
        $list = $this->faq_items ?? [];

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
