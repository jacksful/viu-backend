<?php

namespace App\Filament\Resources\CmsAboutPrinciplesSectionResource\Pages;

use App\Filament\Resources\CmsAboutPrinciplesSectionResource;
use App\Models\CmsAboutPrinciplesSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsAboutPrinciplesSection extends EditRecord
{
    protected static string $resource = CmsAboutPrinciplesSectionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount(CmsAboutPrinciplesSection::singleton()->getKey());
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
        $parameters['record'] ??= CmsAboutPrinciplesSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
