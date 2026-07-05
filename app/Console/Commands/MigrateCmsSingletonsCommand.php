<?php

namespace App\Console\Commands;

use App\Cms\Enums\PageBlockType;
use App\Cms\Enums\PageMenuPosition;
use App\Cms\Enums\PageStatus;
use App\Cms\Legal\DefaultLegalContent;
use App\Cms\Services\PageSectionSync;
use App\Models\CmsAboutHeroSection;
use App\Models\CmsAboutMissionSection;
use App\Models\CmsAboutPrinciplesSection;
use App\Models\CmsHeroSection;
use App\Models\CmsPricingSection;
use App\Models\CmsQaSection;
use App\Models\CmsRecognitionSection;
use App\Models\CmsStrategicWindowSection;
use App\Models\CmsTerritoryZipSection;
use App\Models\Page;
use Illuminate\Console\Command;

class MigrateCmsSingletonsCommand extends Command
{
    protected $signature = 'cms:migrate-singletons {--force : Overwrite existing page sections}';

    protected $description = 'Import legacy singleton CMS sections into page builder pages';

    public function handle(PageSectionSync $sync): int
    {
        $home = $this->upsertPage(
            title: 'Home',
            slug: 'home',
            isHomepage: true,
            sortOrder: 0,
        );

        $about = $this->upsertPage(
            title: 'About',
            slug: 'about',
            isHomepage: false,
            sortOrder: 10,
            menuPositions: [PageMenuPosition::Header->value, PageMenuPosition::Footer->value],
            menuSortOrder: 10,
        );

        $privacy = $this->upsertPage(
            title: 'Privacy policy',
            slug: 'privacy',
            isHomepage: false,
            sortOrder: 20,
            seoTitle: 'Privacy policy | '.config('app.name', 'VIU'),
            bodyClass: 'legal-page',
            menuLabel: 'Privacy',
            menuPositions: [PageMenuPosition::Copyright->value],
            menuSortOrder: 10,
        );

        $terms = $this->upsertPage(
            title: 'Terms of service',
            slug: 'terms',
            isHomepage: false,
            sortOrder: 30,
            seoTitle: 'Terms of service | '.config('app.name', 'VIU'),
            bodyClass: 'legal-page',
            menuLabel: 'Terms',
            menuPositions: [PageMenuPosition::Copyright->value],
            menuSortOrder: 20,
        );

        $this->syncPage($sync, $home, $this->homeBlocks(), 'Home');
        $this->syncPage($sync, $about, $this->aboutBlocks(), 'About');
        $this->syncPage($sync, $privacy, $this->privacyBlocks(), 'Privacy');
        $this->syncPage($sync, $terms, $this->termsBlocks(), 'Terms');

        return self::SUCCESS;
    }

    /**
     * @param  list<array{type: string, data: array<string, mixed>}>  $blocks
     */
    protected function syncPage(PageSectionSync $sync, Page $page, array $blocks, string $label): void
    {
        if ($this->option('force') || $page->sections()->count() === 0) {
            $sync->sync($page, $blocks);
            $this->info("{$label} page sections imported.");
        } else {
            $this->warn("{$label} page already has sections. Use --force to overwrite.");
        }
    }

    /**
     * @param  list<string>  $menuPositions
     */
    protected function upsertPage(
        string $title,
        string $slug,
        bool $isHomepage,
        int $sortOrder,
        ?string $seoTitle = null,
        ?string $bodyClass = null,
        ?string $menuLabel = null,
        array $menuPositions = [],
        int $menuSortOrder = 0,
    ): Page {
        return Page::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'seo_title' => $seoTitle ?? $title.' | '.config('app.name', 'VIU'),
                'status' => PageStatus::Published,
                'is_homepage' => $isHomepage,
                'sort_order' => $sortOrder,
                'body_class' => $bodyClass,
                'menu_label' => $menuLabel,
                'menu_positions' => $menuPositions,
                'menu_sort_order' => $menuSortOrder,
                'published_at' => now(),
            ]
        );
    }

    /**
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    protected function homeBlocks(): array
    {
        $hero = CmsHeroSection::singleton()->only(['title', 'description', 'image_path']);
        $strategic = CmsStrategicWindowSection::singleton()->toArray();
        unset($strategic['id'], $strategic['created_at'], $strategic['updated_at']);
        $territory = CmsTerritoryZipSection::singleton()->toArray();
        unset($territory['id'], $territory['created_at'], $territory['updated_at']);
        $recognition = CmsRecognitionSection::singleton()->toArray();
        unset($recognition['id'], $recognition['created_at'], $recognition['updated_at']);
        $pricing = CmsPricingSection::singleton()->toArray();
        unset($pricing['id'], $pricing['created_at'], $pricing['updated_at']);
        $faq = CmsQaSection::singleton()->toArray();
        unset($faq['id'], $faq['created_at'], $faq['updated_at']);

        return [
            ['type' => PageBlockType::Hero->value, 'data' => $hero],
            ['type' => PageBlockType::StatsBar->value, 'data' => []],
            ['type' => PageBlockType::FeatureStrategicWindow->value, 'data' => $strategic],
            ['type' => PageBlockType::FeatureOneZip->value, 'data' => $territory],
            ['type' => PageBlockType::Recognition->value, 'data' => $recognition],
            ['type' => PageBlockType::Pricing->value, 'data' => $pricing],
            ['type' => PageBlockType::Faq->value, 'data' => $faq],
            ['type' => PageBlockType::CtaBanner->value, 'data' => []],
        ];
    }

    /**
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    protected function aboutBlocks(): array
    {
        $hero = CmsAboutHeroSection::singleton()->only(['badge_text', 'title', 'lead', 'image_path']);
        $mission = CmsAboutMissionSection::singleton()->only(['badge_text', 'headline', 'intro_text', 'body_middle', 'body_last', 'image_path']);
        $principles = CmsAboutPrinciplesSection::singleton()->only(['badge_text', 'heading', 'principles']);

        return [
            ['type' => PageBlockType::AboutHero->value, 'data' => $hero],
            ['type' => PageBlockType::AboutMission->value, 'data' => $mission],
            ['type' => PageBlockType::AboutPrinciples->value, 'data' => $principles],
            ['type' => PageBlockType::CtaBanner->value, 'data' => []],
        ];
    }

    /**
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    protected function privacyBlocks(): array
    {
        return [
            ['type' => PageBlockType::LegalHero->value, 'data' => DefaultLegalContent::privacyHero()],
            ['type' => PageBlockType::LegalContent->value, 'data' => DefaultLegalContent::privacyContent()],
        ];
    }

    /**
     * @return list<array{type: string, data: array<string, mixed>}>
     */
    protected function termsBlocks(): array
    {
        return [
            ['type' => PageBlockType::LegalHero->value, 'data' => DefaultLegalContent::termsHero()],
            ['type' => PageBlockType::LegalContent->value, 'data' => DefaultLegalContent::termsContent()],
        ];
    }
}
