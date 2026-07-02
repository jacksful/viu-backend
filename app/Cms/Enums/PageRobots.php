<?php

namespace App\Cms\Enums;

enum PageRobots: string
{
    case IndexFollow = 'index,follow';
    case NoindexFollow = 'noindex,follow';
    case IndexNofollow = 'index,nofollow';
    case NoindexNofollow = 'noindex,nofollow';

    public function label(): string
    {
        return match ($this) {
            self::IndexFollow => 'Index, follow',
            self::NoindexFollow => 'Noindex, follow',
            self::IndexNofollow => 'Index, nofollow',
            self::NoindexNofollow => 'Noindex, nofollow',
        };
    }
}
