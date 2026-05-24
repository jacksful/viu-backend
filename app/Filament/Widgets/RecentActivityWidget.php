<?php

namespace App\Filament\Widgets;

use App\Models\UploadedZipcode;
use App\Models\User;
use App\Models\UserZipcodeSubscription;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function getActivities(): Collection
    {
        $activities = collect();

        // New leads (customers without subscriptions, created in last 7 days)
        $customersWithSubscriptions = UserZipcodeSubscription::active()
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        $recentLeads = User::where('role', 'customer')
            ->whereNotIn('id', $customersWithSubscriptions)
            ->where('created_at', '>=', now()->subDays(7))
            ->get()
            ->map(function ($user) {
                return (object) [
                    'id' => 'lead_' . $user->id,
                    'type' => 'new_lead',
                    'title' => 'New lead submitted',
                    'description' => $user->name . ' submitted interest',
                    'created_at' => $user->created_at,
                ];
            });

        // Dataset published (published uploaded zipcodes in last 7 days)
        $recentPublishedDatasets = UploadedZipcode::where('status', 'published')
            ->where('updated_at', '>=', now()->subDays(7))
            ->with('zipcode')
            ->get()
            ->map(function ($uploadedZipcode) {
                $monthNames = [
                    1 => 'January',
                    2 => 'February',
                    3 => 'March',
                    4 => 'April',
                    5 => 'May',
                    6 => 'June',
                    7 => 'July',
                    8 => 'August',
                    9 => 'September',
                    10 => 'October',
                    11 => 'November',
                    12 => 'December',
                ];
                $monthName = $monthNames[$uploadedZipcode->month] ?? $uploadedZipcode->month;
                $zipcodeCode = $uploadedZipcode->zipcode?->code ?? 'Unknown';

                return (object) [
                    'id' => 'dataset_' . $uploadedZipcode->id,
                    'type' => 'dataset_published',
                    'title' => 'Dataset published',
                    'description' => "{$monthName} {$uploadedZipcode->year} data published for ZIP {$zipcodeCode}",
                    'created_at' => $uploadedZipcode->updated_at,
                ];
            });

        // New clients (active subscriptions created in last 7 days)
        $recentClients = UserZipcodeSubscription::where('status', 'active')
            ->where('created_at', '>=', now()->subDays(7))
            ->with('user')
            ->get()
            ->map(function ($subscription) {
                $userName = $subscription->user?->name ?? 'Unknown';
                return (object) [
                    'id' => 'client_' . $subscription->id,
                    'type' => 'new_client',
                    'title' => 'New client added',
                    'description' => $userName . ' joined with subscription',
                    'created_at' => $subscription->created_at,
                ];
            });

        return $activities
            ->merge($recentLeads)
            ->merge($recentPublishedDatasets)
            ->merge($recentClients)
            ->sortByDesc('created_at')
            ->take(10);
    }
}
