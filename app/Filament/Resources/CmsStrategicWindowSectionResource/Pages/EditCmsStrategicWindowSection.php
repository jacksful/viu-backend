<?php

namespace App\Filament\Resources\CmsStrategicWindowSectionResource\Pages;

use App\Filament\Resources\CmsStrategicWindowSectionResource;
use App\Models\CmsStrategicWindowSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsStrategicWindowSection extends EditRecord
{
    protected static string $resource = CmsStrategicWindowSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsStrategicWindowSection::singleton();

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
        $parameters['record'] ??= CmsStrategicWindowSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
