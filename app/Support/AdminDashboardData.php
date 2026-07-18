<?php

namespace App\Support;

use App\Filament\Pages\EmailSettings;
use App\Filament\Pages\StripeSettings;
use App\Filament\Pages\TrackingSocialSettingsPage;
use App\Filament\Pages\ZipCodeWiseDataSet;
use App\Filament\Resources\CheckoutHoldResource;
use App\Filament\Resources\StripePaymentResource;
use App\Filament\Resources\UserZipcodeSubscriptionResource;
use App\Filament\Resources\ZipcodeResource;
use App\Models\CheckoutHold;
use App\Models\CustomerIntake;
use App\Models\EmailSetting;
use App\Models\Page;
use App\Models\StripePayment;
use App\Models\StripeSetting;
use App\Models\TrackingSocialSetting;
use App\Models\UploadedZipcode;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Support\Collection;

class AdminDashboardData
{
    public function activeHoldCount(): int
    {
        return CheckoutHold::query()->active()->count();
    }

    /**
     * @return array{amount: float, nearest_expires_label: ?string}
     */
    public function activeHoldSummary(): array
    {
        $holds = CheckoutHold::query()
            ->active()
            ->with('stripePayment')
            ->get();

        $amount = $holds->sum(
            fn (CheckoutHold $hold): float => ($hold->stripePayment?->amount_cents ?? 0) / 100,
        );

        $nearest = $holds
            ->filter(fn (CheckoutHold $hold): bool => $hold->hold_expires_at !== null)
            ->sortBy('hold_expires_at')
            ->first();

        $nearestLabel = null;

        if ($nearest?->hold_expires_at) {
            $hours = (int) now()->diffInHours($nearest->hold_expires_at, false);

            if ($hours < 24) {
                $nearestLabel = max($hours, 1).'h';
            } else {
                $nearestLabel = (int) ceil($hours / 24).'d';
            }
        }

        return [
            'amount' => $amount,
            'nearest_expires_label' => $nearestLabel,
        ];
    }

    public function activeSubscriptionCount(): int
    {
        return UserZipcodeSubscription::query()->active()->count();
    }

    public function subscriptionMonthOverMonthChange(): float
    {
        $current = $this->activeSubscriptionCount();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $previous = UserZipcodeSubscription::query()
            ->where('status', 'active')
            ->where('start_date', '<=', $lastMonthEnd)
            ->where(function ($query) use ($lastMonthEnd): void {
                $query
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>=', $lastMonthEnd);
            })
            ->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * @return array{live: float, test_excluded: float, test_mode: bool, stripe_dashboard_url: string}
     */
    public function revenueSummary(): array
    {
        $settings = StripeSetting::singleton();
        $paidTotal = StripePayment::query()
            ->where('status', 'paid')
            ->sum('amount_cents') / 100;

        $prefix = $settings->test_mode ? 'test/' : '';

        return [
            'live' => $settings->test_mode ? 0.0 : $paidTotal,
            'test_excluded' => $settings->test_mode ? $paidTotal : 0.0,
            'test_mode' => (bool) $settings->test_mode,
            'stripe_dashboard_url' => "https://dashboard.stripe.com/{$prefix}payments",
        ];
    }

    /**
     * @return array<int>
     */
    public function subscribedZipcodeIds(): array
    {
        return UserZipcodeSubscription::query()
            ->active()
            ->pluck('zipcode_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{with_data: int, total: int, missing: int, month_label: string}
     */
    public function subscribedZipDatasetSummary(): array
    {
        $subscribedIds = $this->subscribedZipcodeIds();
        $total = count($subscribedIds);

        if ($total === 0) {
            return [
                'with_data' => 0,
                'total' => 0,
                'missing' => 0,
                'month_label' => now()->format('F'),
            ];
        }

        $withData = UploadedZipcode::query()
            ->where('status', 'published')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->whereHas('datasets')
            ->whereIn('zipcode_id', $subscribedIds)
            ->distinct('zipcode_id')
            ->count('zipcode_id');

        return [
            'with_data' => $withData,
            'total' => $total,
            'missing' => max($total - $withData, 0),
            'month_label' => now()->format('F'),
        ];
    }

    /**
     * @return Collection<int, array{severity: string, message: string, action_label: string, action_url: string}>
     */
    public function attentionItems(): Collection
    {
        $items = collect();
        $emailSandbox = $this->emailUsesSandbox();

        CheckoutHold::query()
            ->active()
            ->with(['stripePayment', 'zipcode'])
            ->orderBy('hold_expires_at')
            ->get()
            ->each(function (CheckoutHold $hold) use ($items, $emailSandbox): void {
                $hours = $hold->hold_expires_at
                    ? (int) now()->diffInHours($hold->hold_expires_at, false)
                    : null;

                $recoveryFailed = $hold->recovery_email_status === CheckoutHold::RECOVERY_STATUS_FAILED;
                $expiringSoon = $hold->isExpiringSoon(24);

                if (! $recoveryFailed && ! $expiringSoon) {
                    return;
                }

                $zip = $hold->zipcode?->code ?? '—';
                $name = $hold->stripePayment?->customer_name ?? 'A prospect';
                $amount = number_format(($hold->stripePayment?->amount_cents ?? 0) / 100, 0);
                $interval = $hold->stripePayment?->billing_interval === Zipcode::BILLING_YEARLY ? 'yr' : 'mo';

                $message = "Hold on ZIP {$zip}";

                if ($hours !== null && $hours <= 24) {
                    $message .= ' expires in '.max($hours, 1).'h';
                }

                if ($recoveryFailed) {
                    $reason = $emailSandbox ? 'email sandbox' : 'delivery failed';
                    $message .= " — {$name}'s recovery email was not delivered ({$reason}).";
                } else {
                    $message .= ' — expiring soon.';
                }

                $message .= " \${$amount}/{$interval} at stake.";

                $items->push([
                    'severity' => $recoveryFailed ? 'danger' : 'warning',
                    'message' => $message,
                    'action_label' => 'Review holds',
                    'action_url' => CheckoutHoldResource::getUrl(),
                ]);
            });

        UserZipcodeSubscription::query()
            ->where('status', 'expired')
            ->where('updated_at', '>=', now()->subDays(30))
            ->with('user')
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function (UserZipcodeSubscription $subscription) use ($items): void {
                $zip = $subscription->zipcodes->first()?->code ?? '—';
                $expiredDate = $subscription->end_date?->format('M j') ?? $subscription->updated_at?->format('M j') ?? 'recently';

                $items->push([
                    'severity' => 'warning',
                    'message' => "Subscription #{$subscription->id} (ZIP {$zip}) expired {$expiredDate} — auto-marked Expired. The ZIP is now available for resale.",
                    'action_label' => 'Review',
                    'action_url' => UserZipcodeSubscriptionResource::getUrl(),
                ]);
            });

        $pendingIntakes = CustomerIntake::query()
            ->whereNotNull('submitted_at')
            ->with('user')
            ->latest('submitted_at')
            ->limit(3)
            ->get();

        if ($pendingIntakes->isNotEmpty()) {
            $latest = $pendingIntakes->first();
            $name = $latest->full_name ?: $latest->user?->name ?: 'A client';
            $submitted = $latest->submitted_at?->format('M j') ?? 'recently';
            $count = $pendingIntakes->count();

            $items->push([
                'severity' => 'warning',
                'message' => "{$count} intake".($count === 1 ? '' : 's')." awaiting review — {$name} submitted brand assets {$submitted}",
                'action_label' => 'Review intake',
                'action_url' => UserZipcodeSubscriptionResource::getUrl(),
            ]);
        }

        Zipcode::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('monthly_price')
                    ->orWhere('monthly_price', '<=', 0);
            })
            ->limit(3)
            ->get()
            ->each(function (Zipcode $zipcode) use ($items): void {
                $items->push([
                    'severity' => 'warning',
                    'message' => "ZIP {$zipcode->code} is published without a monthly price — the website may advertise incomplete pricing.",
                    'action_label' => 'Fix pricing',
                    'action_url' => ZipcodeResource::getUrl(),
                ]);
            });

        return $items->take(6)->values();
    }

    /**
     * @return Collection<int, object>
     */
    public function recentActivities(int $limit = 10): Collection
    {
        $activities = collect();

        CheckoutHold::query()
            ->where('checkout_started_at', '>=', now()->subDays(14))
            ->with(['stripePayment', 'zipcode'])
            ->latest('checkout_started_at')
            ->limit(5)
            ->get()
            ->each(function (CheckoutHold $hold) use ($activities): void {
                $name = $hold->stripePayment?->customer_name ?? 'Someone';
                $zip = $hold->zipcode?->code ?? '—';

                $activities->push((object) [
                    'type' => 'checkout_started',
                    'title' => 'Checkout started',
                    'description' => "{$name} entered checkout for ZIP {$zip}. ZIP locked, ".CheckoutHold::HOLD_DAYS * 24 .'h hold if abandoned.',
                    'created_at' => $hold->checkout_started_at ?? $hold->created_at,
                ]);
            });

        CustomerIntake::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subDays(14))
            ->with('user')
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->each(function (CustomerIntake $intake) use ($activities): void {
                $name = $intake->full_name ?: $intake->user?->name ?: 'A client';

                $activities->push((object) [
                    'type' => 'intake_submitted',
                    'title' => 'Intake submitted',
                    'description' => "{$name} submitted their client intake (brand assets, bio, licensing).",
                    'created_at' => $intake->submitted_at,
                ]);
            });

        StripePayment::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays(14))
            ->with(['zipcode', 'user'])
            ->latest('paid_at')
            ->limit(5)
            ->get()
            ->each(function (StripePayment $payment) use ($activities): void {
                $amount = number_format($payment->amount_cents / 100, 2);
                $zip = $payment->zipcode?->code ?? '—';
                $plan = match ($payment->billing_interval) {
                    Zipcode::BILLING_YEARLY => 'yearly plan',
                    Zipcode::BILLING_MONTHLY => 'monthly plan',
                    default => 'plan',
                };
                $name = $payment->customer_name ?: $payment->user?->name ?: 'Client';
                $testSuffix = StripeSetting::singleton()->test_mode ? ' (test mode)' : '';

                $activities->push((object) [
                    'type' => 'payment_received',
                    'title' => 'Payment received',
                    'description' => "\${$amount}{$testSuffix} · ZIP {$zip}, {$plan} · {$name} became a client.",
                    'created_at' => $payment->paid_at ?? $payment->created_at,
                ]);
            });

        Page::query()
            ->where('updated_at', '>=', now()->subDays(14))
            ->whereColumn('updated_at', '>', 'created_at')
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->each(function (Page $page) use ($activities): void {
                $activities->push((object) [
                    'type' => 'page_updated',
                    'title' => 'Page updated',
                    'description' => "Page '{$page->title}' updated — content or settings changed.",
                    'created_at' => $page->updated_at,
                ]);
            });

        return $activities
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array{status: string, title: string, description: string, action_label: ?string, action_url: ?string}>
     */
    public function launchReadinessItems(): Collection
    {
        $emailSettings = EmailSetting::singleton();
        $stripeSettings = StripeSetting::singleton();
        $trackingSettings = TrackingSocialSetting::singleton();

        $emailSandbox = $this->emailUsesSandbox();
        $analyticsReady = $trackingSettings->google_analytics_enabled
            && filled($trackingSettings->google_analytics_measurement_id);

        $leadCaptureReady = $stripeSettings->enabled
            && Zipcode::query()->where('is_active', true)->where('yearly_price', '>', 0)->exists()
            && ! $stripeSettings->test_mode;

        return collect([
            [
                'status' => $emailSandbox ? 'danger' : 'success',
                'title' => 'Email delivery',
                'description' => $emailSandbox
                    ? 'SMTP points to a sandbox ('.$this->emailProviderLabel($emailSettings).') — messages are not being delivered to real inboxes.'
                    : 'SMTP is configured for production delivery.',
                'action_label' => $emailSandbox ? 'Fix email settings →' : null,
                'action_url' => $emailSandbox ? EmailSettings::getUrl() : null,
            ],
            [
                'status' => ($stripeSettings->test_mode || ! $stripeSettings->enabled) ? 'warning' : 'success',
                'title' => 'Stripe payments',
                'description' => ! $stripeSettings->enabled
                    ? 'Stripe checkout is disabled — visitors cannot purchase territories.'
                    : ($stripeSettings->test_mode
                        ? 'Test mode is ON — visitors cannot complete real purchases.'
                        : 'Live Stripe checkout is enabled.'),
                'action_label' => ($stripeSettings->test_mode || ! $stripeSettings->enabled) ? 'Review Stripe config →' : null,
                'action_url' => ($stripeSettings->test_mode || ! $stripeSettings->enabled) ? StripeSettings::getUrl() : null,
            ],
            [
                'status' => $analyticsReady ? 'success' : 'warning',
                'title' => 'Analytics & tracking',
                'description' => $analyticsReady
                    ? 'GA4 property is connected ('.$trackingSettings->google_analytics_measurement_id.').'
                    : 'Google Analytics is not fully configured yet.',
                'action_label' => $analyticsReady ? null : 'Review tracking settings →',
                'action_url' => $analyticsReady ? null : TrackingSocialSettingsPage::getUrl(),
            ],
            [
                'status' => $leadCaptureReady ? 'success' : 'warning',
                'title' => 'Lead capture',
                'description' => $leadCaptureReady
                    ? 'End-to-end pipeline verified (ZIP check → form → Stripe).'
                    : 'Lead-to-checkout pipeline needs verification before launch.',
                'action_label' => $leadCaptureReady ? null : 'Review Stripe config →',
                'action_url' => $leadCaptureReady ? null : StripeSettings::getUrl(),
            ],
        ]);
    }

    public function emailUsesSandbox(): bool
    {
        $settings = EmailSetting::singleton();

        if ($settings->mail_mailer === 'log') {
            return true;
        }

        $host = strtolower((string) $settings->mail_host);

        return str_contains($host, 'mailtrap')
            || str_contains($host, 'sandbox')
            || str_contains($host, 'mailhog')
            || str_contains($host, '127.0.0.1')
            || str_contains($host, 'localhost');
    }

    protected function emailProviderLabel(EmailSetting $settings): string
    {
        $host = strtolower((string) $settings->mail_host);

        if (str_contains($host, 'mailtrap')) {
            return 'Mailtrap';
        }

        if ($settings->mail_mailer === 'log') {
            return 'log driver';
        }

        return $settings->mail_host ?: 'sandbox';
    }

    public function formattedDate(): string
    {
        return now()->format('l, F j, Y');
    }

    public function holdsUrl(): string
    {
        return CheckoutHoldResource::getUrl();
    }

    public function subscriptionsUrl(): string
    {
        return UserZipcodeSubscriptionResource::getUrl();
    }

    public function paymentsUrl(): string
    {
        return StripePaymentResource::getUrl();
    }

    public function datasetsUrl(): string
    {
        return ZipCodeWiseDataSet::getUrl();
    }
}
