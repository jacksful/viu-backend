<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Cms\Services\PageSectionSync;
use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    /** @var list<array{type?: string, data?: array<string, mixed>}>|null */
    protected ?array $pendingSections = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingSections = $data['sections'] ?? null;
        unset($data['sections']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(PageSectionSync::class)->sync($this->record, $this->pendingSections);
    }
}
