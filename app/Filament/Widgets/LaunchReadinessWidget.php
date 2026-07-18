<?php

namespace App\Filament\Widgets;

use App\Support\AdminDashboardData;
use Filament\Actions\Action;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LaunchReadinessWidget extends SchemaWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 4,
    ];

    public function content(Schema $schema): Schema
    {
        $items = app(AdminDashboardData::class)->launchReadinessItems();

        return $schema
            ->components([
                Section::make('Launch readiness')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->compact()
                    ->schema(
                        $items
                            ->map(function (array $item, int $index): Callout {
                                $callout = $this->listCallout(
                                    heading: $item['title'],
                                    description: $item['description'],
                                )
                                    ->icon($this->statusIcon($item['status']))
                                    ->iconColor($item['status']);

                                if (filled($item['action_label'] ?? null) && filled($item['action_url'] ?? null)) {
                                    $callout->actions([
                                        Action::make('launch_readiness_'.$index)
                                            ->label($item['action_label'])
                                            ->url($item['action_url'])
                                            ->link(),
                                    ]);
                                }

                                return $callout;
                            })
                            ->all(),
                    ),
            ]);
    }

    protected function statusIcon(string $status): string
    {
        return match ($status) {
            'success' => 'heroicon-o-check-circle',
            'danger' => 'heroicon-o-x-circle',
            default => 'heroicon-o-exclamation-circle',
        };
    }
}
