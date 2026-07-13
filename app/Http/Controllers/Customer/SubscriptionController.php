<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use App\Services\StripeSubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        protected StripeSubscriptionService $stripeSubscriptions,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $subscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.subscription', compact('subscriptions'));
    }

    public function getData(): JsonResponse
    {
        $user = Auth::user();

        $activeSubscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $zipcodeIds = collect();
        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->zipcode_ids) {
                $zipcodeIds = $zipcodeIds->merge($subscription->zipcode_ids);
            }
        }
        $zipcodeIds = $zipcodeIds->unique()->values()->all();

        $subscriptionSummaries = $activeSubscriptions->map(function (UserZipcodeSubscription $subscription) {
            $subscriptionZipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
            $interval = $subscription->billing_interval ?: Zipcode::BILLING_MONTHLY;
            $amount = $subscriptionZipcodes->sum(function (Zipcode $zipcode) use ($interval): float {
                if ($interval === Zipcode::BILLING_YEARLY) {
                    return (float) ($zipcode->yearly_price ?? 0);
                }

                return (float) ($zipcode->monthly_price ?? 0);
            });

            return [
                'subscription' => $subscription,
                'zipcodes' => $subscriptionZipcodes,
                'interval' => $interval,
                'amount' => $amount,
            ];
        });

        $totalMonthlyEquivalent = $subscriptionSummaries->sum(function (array $summary): float {
            if ($summary['interval'] === Zipcode::BILLING_YEARLY) {
                return $summary['amount'] / 12;
            }

            return $summary['amount'];
        });

        $nextBillingDate = $this->resolveNextBillingDate($activeSubscriptions);

        $billingHistory = [];
        $allSubscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($allSubscriptions as $subscription) {
            $subscriptionZipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
            $zipcodeCount = $subscriptionZipcodes->count();
            $interval = $subscription->billing_interval ?: Zipcode::BILLING_MONTHLY;
            $amount = $subscriptionZipcodes->sum(function (Zipcode $zipcode) use ($interval): float {
                if ($interval === Zipcode::BILLING_YEARLY) {
                    return (float) ($zipcode->yearly_price ?? 0);
                }

                return (float) ($zipcode->monthly_price ?? 0);
            });

            $startDate = Carbon::parse($subscription->start_date);
            $endDate = $subscription->revenueEndAt();
            $currentDate = $startDate->copy();
            $invoiceCount = 0;
            $stepMethod = $interval === Zipcode::BILLING_YEARLY ? 'addYear' : 'addMonth';
            $periodLabel = $interval === Zipcode::BILLING_YEARLY ? 'Yearly Subscription' : 'Monthly Subscription';

            while ($currentDate->lte($endDate) && $invoiceCount < 3) {
                $billingHistory[] = [
                    'id' => $subscription->id.'_'.$currentDate->format('Y-m'),
                    'date' => $currentDate->format('M d, Y'),
                    'description' => $zipcodeCount.' ZIP Code'.($zipcodeCount !== 1 ? 's' : '').' - '.$periodLabel,
                    'amount' => number_format($amount, 2),
                    'status' => 'Paid',
                ];
                $currentDate->{$stepMethod}();
                $invoiceCount++;
            }
        }

        usort($billingHistory, fn (array $a, array $b): int => strtotime($b['date']) - strtotime($a['date']));
        $billingHistory = array_slice($billingHistory, 0, 10);

        return response()->json([
            'memberSince' => $user->created_at->format('F Y'),
            'subscriptionStart' => $activeSubscriptions->sortBy('start_date')->first()?->formattedStartDate() ?? '—',
            'subscriptionEnd' => $activeSubscriptions->sortByDesc('end_date')->first()?->formattedEndDate() ?? 'Ongoing',
            'nextBillingDate' => $nextBillingDate,
            'zipcodeCount' => count($zipcodeIds),
            'totalMonthly' => number_format($totalMonthlyEquivalent, 2),
            'zipcodes' => $subscriptionSummaries->flatMap(function (array $summary) {
                return $summary['zipcodes']->map(function (Zipcode $zipcode) use ($summary) {
                    $subscription = $summary['subscription'];
                    $interval = $summary['interval'];
                    $price = $interval === Zipcode::BILLING_YEARLY
                        ? $zipcode->yearly_price
                        : $zipcode->monthly_price;

                    return [
                        'id' => $zipcode->id,
                        'subscription_id' => $subscription->id,
                        'code' => $zipcode->code,
                        'city' => $zipcode->city ?? 'N/A',
                        'state' => $zipcode->state ?? 'N/A',
                        'billing_interval' => $interval,
                        'subscription_start' => $subscription->formattedStartDate(),
                        'subscription_end' => $subscription->formattedEndDate(),
                        'price' => $price === null ? null : number_format((float) $price, 2),
                        'price_label' => $interval === Zipcode::BILLING_YEARLY ? '/year' : '/month',
                        'cancel_at_period_end' => (bool) $subscription->cancel_at_period_end,
                        'can_cancel' => $this->canCancel($subscription),
                        'can_reactivate' => $this->canReactivate($subscription),
                        'upgrade_option' => $this->resolveUpgradeOption($zipcode, $interval, $subscription),
                    ];
                });
            })->values()->all(),
            'billingHistory' => $billingHistory,
        ]);
    }

    public function cancel(UserZipcodeSubscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        try {
            $this->stripeSubscriptions->cancelAtPeriodEnd($subscription);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $subscription->refresh();

        return response()->json([
            'message' => 'Your subscription has been scheduled to cancel. You keep full access until '.$subscription->formattedEndDate().'.',
            'subscription_end' => $subscription->formattedEndDate(),
            'cancel_at_period_end' => true,
        ]);
    }

    public function reactivate(UserZipcodeSubscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        try {
            $this->stripeSubscriptions->reactivateSubscription($subscription);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $subscription->refresh();

        return response()->json([
            'message' => 'Your subscription has been reactivated and will continue renewing.',
            'cancel_at_period_end' => false,
        ]);
    }

    public function upgrade(Request $request, UserZipcodeSubscription $subscription): JsonResponse
    {
        $this->authorizeSubscription($subscription);

        $validated = $request->validate([
            'billing_interval' => ['required', 'in:month,year'],
        ]);

        try {
            $this->stripeSubscriptions->upgradeBillingInterval(
                $subscription,
                $validated['billing_interval'],
            );
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $subscription->refresh();

        $intervalLabel = $subscription->billing_interval === Zipcode::BILLING_YEARLY
            ? 'yearly'
            : 'monthly';

        return response()->json([
            'message' => "Your subscription has been upgraded to {$intervalLabel} billing.",
            'billing_interval' => $subscription->billing_interval,
        ]);
    }

    protected function authorizeSubscription(UserZipcodeSubscription $subscription): void
    {
        abort_unless(
            $subscription->user_id === Auth::id(),
            403,
            'You do not have access to this subscription.',
        );
    }

    protected function canCancel(UserZipcodeSubscription $subscription): bool
    {
        return $subscription->status === 'active'
            && filled($subscription->stripe_subscription_id)
            && ! $subscription->cancel_at_period_end;
    }

    protected function canReactivate(UserZipcodeSubscription $subscription): bool
    {
        return $subscription->status === 'active'
            && filled($subscription->stripe_subscription_id)
            && $subscription->cancel_at_period_end;
    }

    protected function resolveUpgradeOption(Zipcode $zipcode, string $currentInterval, UserZipcodeSubscription $subscription): ?array
    {
        if ($subscription->status !== 'active' || $subscription->cancel_at_period_end) {
            return null;
        }

        if (! filled($subscription->stripe_subscription_id)) {
            return null;
        }

        $targetInterval = $currentInterval === Zipcode::BILLING_YEARLY
            ? Zipcode::BILLING_MONTHLY
            : Zipcode::BILLING_YEARLY;

        if (! $zipcode->hasStripePriceForInterval($targetInterval)) {
            return null;
        }

        $plan = $zipcode->resolveBillingPlan($targetInterval);
        $isUpgrade = $targetInterval === Zipcode::BILLING_YEARLY;

        return [
            'interval' => $targetInterval,
            'label' => $plan['label'],
            'price' => number_format($plan['amount'], 2),
            'price_label' => $targetInterval === Zipcode::BILLING_YEARLY ? '/year' : '/month',
            'action_label' => $isUpgrade ? 'Upgrade to Yearly' : 'Switch to Monthly',
        ];
    }

    protected function resolveNextBillingDate($subscriptions): string
    {
        $soonest = null;

        foreach ($subscriptions as $subscription) {
            if (! $subscription->end_date) {
                continue;
            }

            $next = Carbon::parse($subscription->end_date);

            if (! $soonest || $next->lt($soonest)) {
                $soonest = $next;
            }
        }

        return ($soonest ?? Carbon::now()->addMonth())->format('M d, Y');
    }
}
