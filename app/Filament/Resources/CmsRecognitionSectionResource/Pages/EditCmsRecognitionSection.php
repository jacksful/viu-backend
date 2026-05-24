<?php

namespace App\Filament\Resources\CmsRecognitionSectionResource\Pages;

use App\Filament\Resources\CmsRecognitionSectionResource;
use App\Models\CmsRecognitionSection;
use Filament\Resources\Pages\EditRecord;

class EditCmsRecognitionSection extends EditRecord
{
    protected static string $resource = CmsRecognitionSectionResource::class;

    public function mount(int|string $record): void
    {
        $singleton = CmsRecognitionSection::singleton();

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
        $parameters['record'] ??= CmsRecognitionSection::singleton()->getRouteKey();

        return parent::getNavigationUrl($parameters);
    }
}
