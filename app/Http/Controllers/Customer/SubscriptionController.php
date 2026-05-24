<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription page
     */
    public function index()
    {
        $user = Auth::user();
        $subscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('customer.subscription', compact('subscriptions'));
    }

    /**
     * Get subscription data for modal (API endpoint)
     */
    public function getData()
    {
        $user = Auth::user();
        
        // Get active subscriptions
        $activeSubscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Collect all unique zipcodes from active subscriptions
        $zipcodeIds = collect();
        foreach ($activeSubscriptions as $subscription) {
            if ($subscription->zipcode_ids) {
                $zipcodeIds = $zipcodeIds->merge($subscription->zipcode_ids);
            }
        }
        $zipcodeIds = $zipcodeIds->unique()->values()->all();
        
        // Get zipcode details
        $zipcodes = Zipcode::whereIn('id', $zipcodeIds)->get();
        
        // Calculate total monthly subscription
        $totalMonthly = $zipcodes->sum(fn (Zipcode $z): float => (float) ($z->monthly_price ?? 0));
        
        // Calculate next billing date (assuming monthly billing on the 15th)
        $nextBillingDate = Carbon::now()->day >= 15 
            ? Carbon::now()->addMonth()->day(15)->format('M d, Y')
            : Carbon::now()->day(15)->format('M d, Y');
        
        // Generate billing history from subscriptions
        $billingHistory = [];
        $allSubscriptions = UserZipcodeSubscription::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($allSubscriptions as $subscription) {
            $subscriptionZipcodes = Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
            $zipcodeCount = $subscriptionZipcodes->count();
            $amount = $subscriptionZipcodes->sum(fn (Zipcode $z): float => (float) ($z->monthly_price ?? 0));
            
            // Generate invoice dates (monthly from subscription start date)
            $startDate = Carbon::parse($subscription->start_date);
            $endDate = $subscription->end_date ? Carbon::parse($subscription->end_date) : Carbon::now();
            
            // Generate up to 3 months of billing history per subscription
            $currentDate = $startDate->copy();
            $invoiceCount = 0;
            while ($currentDate->lte($endDate) && $invoiceCount < 3) {
                $billingHistory[] = [
                    'id' => $subscription->id . '_' . $currentDate->format('Y-m'),
                    'date' => $currentDate->format('M d, Y'),
                    'description' => $zipcodeCount . ' ZIP Code' . ($zipcodeCount !== 1 ? 's' : '') . ' - Monthly Subscription',
                    'amount' => number_format($amount, 2),
                    'status' => 'Paid'
                ];
                $currentDate->addMonth();
                $invoiceCount++;
            }
        }
        
        // Sort billing history by date (newest first) and limit to 10
        usort($billingHistory, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        $billingHistory = array_slice($billingHistory, 0, 10);
        
        return response()->json([
            'memberSince' => $user->created_at->format('F Y'),
            'nextBillingDate' => $nextBillingDate,
            'zipcodeCount' => count($zipcodeIds),
            'totalMonthly' => number_format($totalMonthly, 2),
            'zipcodes' => $zipcodes->map(function($zipcode) {
                return [
                    'id' => $zipcode->id,
                    'code' => $zipcode->code,
                    'city' => $zipcode->city ?? 'N/A',
                    'state' => $zipcode->state ?? 'N/A',
                    'monthly_price' => $zipcode->monthly_price === null
                        ? null
                        : number_format((float) $zipcode->monthly_price, 2)
                ];
            })->values()->all(),
            'billingHistory' => $billingHistory
        ]);
    }
}

