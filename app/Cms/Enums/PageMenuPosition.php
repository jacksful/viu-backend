<?php

namespace App\Cms\Enums;

enum PageMenuPosition: string
{
    case Header = 'header';
    case Footer = 'footer';
    case Copyright = 'copyright';

    public function label(): string
    {
        return match ($this) {
            self::Header => 'Header menu',
            self::Footer => 'Footer menu',
            self::Copyright => 'Copyright menu',
        };
    }
}
