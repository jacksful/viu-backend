<?php

namespace App\Filament\Resources\CmsHeroSectionResource\Pages;

use App\Filament\Resources\CmsHeroSectionResource;
use App\Models\CmsHeroSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsHeroSection extends EditRecord
{
    protected static string $resource = CmsHeroSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsHeroSection::singleton();

        parent::mount($singleton->getKey());
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
        $parameters['record'] ??= CmsHeroSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
