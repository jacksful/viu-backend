<?php

namespace App\Filament\Resources\UploadedZipcodeResource\Pages;

use App\Filament\Resources\UploadedZipcodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageUploadedZipcodes extends ManageRecords
{
    protected static string $resource = UploadedZipcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create Uploaded ZIP Code')
                ->modalWidth('5xl')
                ->modalSubmitActionLabel('Create')
                ->modalCancelActionLabel('Cancel'),
        ];
    }
}
