<?php

namespace App\Filament\Widgets;

use App\Support\AdminDashboardData;
use Filament\Actions\Action;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DashboardMainColumnWidget extends SchemaWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 8,
    ];

    public function content(Schema $schema): Schema
    {
        $dashboard = app(AdminDashboardData::class);

        return $schema
            ->components([
                $this->needsAttentionSection($dashboard),
                $this->recentActivitySection($dashboard),
            ]);
    }

    protected function needsAttentionSection(AdminDashboardData $dashboard): Section
    {
        $items = $dashboard->attentionItems();

        if ($items->isEmpty()) {
            return Section::make('Needs attention')
                ->icon('heroicon-o-exclamation-triangle')
                ->compact()
                ->schema([
                    EmptyState::make('All clear')
                        ->description('Nothing needs attention right now.')
                        ->icon('heroicon-o-check-circle')
                        ->compact(),
                ]);
        }

        return Section::make('Needs attention')
            ->icon('heroicon-o-exclamation-triangle')
            ->compact()
            ->schema(
                $items
                    ->map(function (array $item, int $index): Callout {
                        return $this->listCallout(description: $item['message'])
                            ->icon($item['severity'] === 'danger'
                                ? 'heroicon-o-exclamation-circle'
                                : 'heroicon-o-exclamation-triangle')
                            ->iconColor($item['severity'] === 'danger' ? 'danger' : 'warning')
                            ->actions([
                                Action::make('review_'.$index)
                                    ->label($item['action_label'])
                                    ->url($item['action_url'])
                                    ->link(),
                            ]);
                    })
                    ->all(),
            );
    }

    protected function recentActivitySection(AdminDashboardData $dashboard): Section
    {
        $activities = $dashboard->recentActivities();

        if ($activities->isEmpty()) {
            return Section::make('Recent activity')
                ->icon('heroicon-o-bell')
                ->compact()
                ->schema([
                    EmptyState::make('No recent activity')
                        ->description('Activity from the last two weeks will appear here.')
                        ->icon('heroicon-o-bell-slash')
                        ->compact(),
                ]);
        }

        return Section::make('Recent activity')
            ->icon('heroicon-o-bell')
            ->compact()
            ->schema(
                $activities
                    ->map(fn (object $activity): Callout => $this->makeActivityCallout($activity))
                    ->all(),
            );
    }

    protected function makeActivityCallout(object $activity): Callout
    {
        return $this->listCallout(
            heading: $activity->title,
            description: $activity->description.' · '.$activity->created_at?->format('F j, g:i A'),
        )
            ->icon($this->activityIcon($activity->type))
            ->iconColor('gray');
    }

    protected function activityIcon(string $type): string
    {
        return match ($type) {
            'checkout_started' => 'heroicon-o-shopping-cart',
            'intake_submitted' => 'heroicon-o-document-text',
            'payment_received' => 'heroicon-o-banknotes',
            'page_updated' => 'heroicon-o-pencil-square',
            default => 'heroicon-o-bell',
        };
    }
}
