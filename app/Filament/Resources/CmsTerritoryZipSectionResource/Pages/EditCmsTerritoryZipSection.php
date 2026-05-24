<?php

namespace App\Filament\Resources\CmsTerritoryZipSectionResource\Pages;

use App\Filament\Resources\CmsTerritoryZipSectionResource;
use App\Models\CmsTerritoryZipSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsTerritoryZipSection extends EditRecord
{
    protected static string $resource = CmsTerritoryZipSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsTerritoryZipSection::singleton();

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
        $parameters['record'] ??= CmsTerritoryZipSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
