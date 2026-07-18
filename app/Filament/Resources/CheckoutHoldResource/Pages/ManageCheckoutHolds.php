<?php

namespace App\Filament\Resources\CheckoutHoldResource\Pages;

use App\Filament\Resources\CheckoutHoldResource;
use App\Models\CheckoutHold;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ManageCheckoutHolds extends ManageRecords
{
    protected static string $resource = CheckoutHoldResource::class;

    protected static ?string $title = 'Checkouts & Holds';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Failed or cancelled checkouts hold the ZIP for 4 days with an automatic recovery email — then the ZIP releases itself and the waitlist is notified.';
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active holds')
                ->badge(fn (): int => CheckoutHold::query()->active()->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->active()),
            'history' => Tab::make('History')
                ->badge(fn (): int => CheckoutHold::query()->history()->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->history()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
