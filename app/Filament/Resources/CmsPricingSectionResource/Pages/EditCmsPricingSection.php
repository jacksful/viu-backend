<?php

namespace App\Filament\Resources\CmsPricingSectionResource\Pages;

use App\Filament\Resources\CmsPricingSectionResource;
use App\Models\CmsPricingSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsPricingSection extends EditRecord
{
    protected static string $resource = CmsPricingSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsPricingSection::singleton();

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
        $parameters['record'] ??= CmsPricingSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
