<?php

namespace App\Filament\Resources\CmsQaSectionResource\Pages;

use App\Filament\Resources\CmsQaSectionResource;
use App\Models\CmsQaSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsQaSection extends EditRecord
{
    protected static string $resource = CmsQaSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsQaSection::singleton();

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
        $parameters['record'] ??= CmsQaSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
