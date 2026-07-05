<?php

namespace App\Cms\Support;

use App\Cms\Blocks\AboutHeroBlock;
use App\Cms\Blocks\AboutMissionBlock;
use App\Cms\Blocks\AboutPrinciplesBlock;
use App\Cms\Blocks\CtaBannerBlock;
use App\Cms\Blocks\FaqBlock;
use App\Cms\Blocks\FeatureOneZipBlock;
use App\Cms\Blocks\FeatureStrategicWindowBlock;
use App\Cms\Blocks\HeroBlock;
use App\Cms\Blocks\LegalContentBlock;
use App\Cms\Blocks\LegalHeroBlock;
use App\Cms\Blocks\PricingBlock;
use App\Cms\Blocks\RecognitionBlock;
use App\Cms\Blocks\StatsBarBlock;
use App\Cms\Contracts\PageBlock;
use App\Cms\Enums\PageBlockType;
use Filament\Forms\Components\Builder\Block;

class BlockRegistry
{
    /**
     * @return list<class-string<PageBlock>>
     */
    public static function blocks(): array
    {
        return [
            HeroBlock::class,
            StatsBarBlock::class,
            FeatureStrategicWindowBlock::class,
            FeatureOneZipBlock::class,
            RecognitionBlock::class,
            PricingBlock::class,
            FaqBlock::class,
            CtaBannerBlock::class,
            AboutHeroBlock::class,
            AboutMissionBlock::class,
            AboutPrinciplesBlock::class,
            LegalHeroBlock::class,
            LegalContentBlock::class,
        ];
    }

    /**
     * @return array<string, class-string<PageBlock>>
     */
    public static function map(): array
    {
        $map = [];

        foreach (self::blocks() as $blockClass) {
            $map[$blockClass::type()->value] = $blockClass;
        }

        return $map;
    }

    /**
     * @return class-string<PageBlock>|null
     */
    public static function resolveClass(PageBlockType|string $type): ?string
    {
        $key = $type instanceof PageBlockType ? $type->value : $type;

        return self::map()[$key] ?? null;
    }

    /**
     * @deprecated Use resolveClass() for static block APIs.
     */
    public static function resolve(PageBlockType|string $type): ?PageBlock
    {
        $class = self::resolveClass($type);

        return $class ? app($class) : null;
    }

    /**
     * @return list<Block>
     */
    public static function filamentBlocks(): array
    {
        return array_map(
            fn (string $blockClass) => $blockClass::filamentBlock(),
            self::blocks()
        );
    }
}
