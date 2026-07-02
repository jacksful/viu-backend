<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Cms\Services\PageSectionSync;
use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\URL;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    /** @var list<array{type?: string, data?: array<string, mixed>}>|null */
    protected ?array $pendingSections = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->url(fn (Page $record): string => URL::temporarySignedRoute(
                    'pages.preview',
                    now()->addHour(),
                    ['page' => $record]
                ))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['sections'] = app(PageSectionSync::class)->toBuilderState($this->record);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingSections = $data['sections'] ?? null;
        unset($data['sections']);

        return $data;
    }

    protected function afterSave(): void
    {
        app(PageSectionSync::class)->sync($this->record, $this->pendingSections);
    }
}
