<?php

namespace App\Filament\Resources\UserZipcodeSubscriptionResource\Pages;

use App\Filament\Resources\UserZipcodeSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms\Components;
use Filament\Schemas\Components\Grid;
use App\Models\Zipcode;

class ManageUserZipcodeSubscriptions extends ManageRecords
{
    protected static string $resource = UserZipcodeSubscriptionResource::class;

    protected static ?string $title = 'Clients';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create New Client Subscription')
                ->modalWidth('5xl')
                ->modalSubmitActionLabel('Create Subscription'),
        ];
    }
}
