<?php

namespace App\Filament\Resources\ZipcodeResource\Pages;

use App\Exports\ZipcodesImportTemplateExport;
use App\Filament\Resources\ZipcodeResource;
use App\Imports\ZipcodesImport;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManageZipcodes extends ManageRecords
{
    protected static string $resource = ZipcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadZipcodesExample')
                ->label('Example Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->tooltip('Download a sample spreadsheet with headers and example rows.')
                ->action(fn (): \Symfony\Component\HttpFoundation\BinaryFileResponse => Excel::download(
                    new ZipcodesImportTemplateExport,
                    'zipcodes-import-example.xlsx'
                )),

            Actions\Action::make('importZipcodes')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import zipcodes')
                ->modalDescription('First row must be column titles. Matches the Example Excel template. Existing ZIP codes are updated by zipcode.')
                ->modalSubmitActionLabel('Import')
                ->modalWidth('lg')
                ->form([
                    Components\FileUpload::make('import_file')
                        ->label('Spreadsheet')
                        ->required()
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            '.csv',
                            '.xls',
                            '.xlsx',
                        ])
                        ->disk('local')
                        ->directory('imports/zipcodes')
                        ->visibility('private')
                        ->maxSize(20480),
                ])
                ->action(function (array $data): void {
                    $relative = $data['import_file'] ?? null;

                    if (! is_string($relative) || $relative === '') {
                        Notification::make()
                            ->title('Import failed')
                            ->body('No file was uploaded.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (! Storage::disk('local')->exists($relative)) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('The uploaded file could not be found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $absolutePath = Storage::disk('local')->path($relative);

                    try {
                        $import = new ZipcodesImport;
                        Excel::import($import, $absolutePath);

                        $skipped = $import->rowsSkipped ? " {$import->rowsSkipped} row(s) skipped (missing zipcode while other cells were filled)." : '';

                        Notification::make()
                            ->title('Import completed')
                            ->body("Processed {$import->rowsImported} row(s).{$skipped}")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->modalHeading('Create New Zipcode')
                ->modalWidth('5xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Cancel'),
        ];
    }
}
