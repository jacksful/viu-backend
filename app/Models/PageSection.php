<?php

namespace App\Models;

use App\Cms\Enums\PageBlockType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'content',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'type' => PageBlockType::class,
            'content' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
