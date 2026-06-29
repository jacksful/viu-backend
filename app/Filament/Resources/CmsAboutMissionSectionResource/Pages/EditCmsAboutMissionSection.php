<?php

namespace App\Filament\Resources\CmsAboutMissionSectionResource\Pages;

use App\Filament\Resources\CmsAboutMissionSectionResource;
use App\Models\CmsAboutMissionSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsAboutMissionSection extends EditRecord
{
    protected static string $resource = CmsAboutMissionSectionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount(CmsAboutMissionSection::singleton()->getKey());
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
        $parameters['record'] ??= CmsAboutMissionSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
