<?php

namespace App\Cms\Enums;

enum PageBlockType: string
{
    case Hero = 'hero';
    case StatsBar = 'stats_bar';
    case FeatureStrategicWindow = 'feature_strategic_window';
    case FeatureOneZip = 'feature_one_zip';
    case Recognition = 'recognition';
    case Pricing = 'pricing';
    case Faq = 'faq';
    case CtaBanner = 'cta_banner';
    case AboutHero = 'about_hero';
    case AboutMission = 'about_mission';
    case AboutPrinciples = 'about_principles';
    case LegalHero = 'legal_hero';
    case LegalContent = 'legal_content';

    public function label(): string
    {
        return match ($this) {
            self::Hero => 'Hero',
            self::StatsBar => 'Stats bar',
            self::FeatureStrategicWindow => 'Strategic window (Be first)',
            self::FeatureOneZip => 'One ZIP territory',
            self::Recognition => 'Recognition',
            self::Pricing => 'Pricing',
            self::Faq => 'FAQ',
            self::CtaBanner => 'CTA banner',
            self::AboutHero => 'About hero',
            self::AboutMission => 'About mission',
            self::AboutPrinciples => 'About principles',
            self::LegalHero => 'Legal hero',
            self::LegalContent => 'Legal content',
        };
    }
}
