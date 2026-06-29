<?php

namespace App\Filament\Resources\CmsAboutHeroSectionResource\Pages;

use App\Filament\Resources\CmsAboutHeroSectionResource;
use App\Models\CmsAboutHeroSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsAboutHeroSection extends EditRecord
{
    protected static string $resource = CmsAboutHeroSectionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount(CmsAboutHeroSection::singleton()->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function getNavigationUrl(array $parameters = []): string
    {
        $parameters['record'] ??= CmsAboutHeroSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
