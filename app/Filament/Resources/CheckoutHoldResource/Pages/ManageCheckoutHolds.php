<?php

namespace App\Filament\Resources\CheckoutHoldResource\Pages;

use App\Filament\Resources\CheckoutHoldResource;
use App\Models\CheckoutHold;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
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

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table->searchPlaceholder('Search holds...');
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

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->contained(true)
                    ->schema([
                        $this->getTabsContentComponent(),
                        RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE),
                        EmbeddedTable::make(),
                        RenderHook::make(PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER),
                    ]),
            ]);
    }

    public function getTabsContentComponent(): Component
    {
        $tabs = $this->getCachedTabs();

        return Tabs::make()
            ->key('resourceTabs')
            ->livewireProperty('activeTab')
            ->contained(true)
            ->tabs($tabs)
            ->hidden(empty($tabs));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
