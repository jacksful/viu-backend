<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $subscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.subscription', compact('subscriptions'));
    }

    public function getData()
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

        $zipcodes = Zipcode::whereIn('id', $zipcodeIds)->get()->keyBy('id');

        $subscriptionSummaries = $activeSubscriptions->map(function (UserZipcodeSubscription $subscription) use ($zipcodes) {
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
                    $interval = $summary['interval'];
                    $price = $interval === Zipcode::BILLING_YEARLY
                        ? $zipcode->yearly_price
                        : $zipcode->monthly_price;

                    return [
                        'id' => $zipcode->id,
                        'code' => $zipcode->code,
                        'city' => $zipcode->city ?? 'N/A',
                        'state' => $zipcode->state ?? 'N/A',
                        'billing_interval' => $interval,
                        'subscription_start' => $summary['subscription']->formattedStartDate(),
                        'subscription_end' => $summary['subscription']->formattedEndDate(),
                        'price' => $price === null ? null : number_format((float) $price, 2),
                        'price_label' => $interval === Zipcode::BILLING_YEARLY ? '/year' : '/month',
                    ];
                });
            })->values()->all(),
            'billingHistory' => $billingHistory,
        ]);
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
