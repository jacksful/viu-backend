<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\UserZipcodeSubscription;
use App\Models\Zipcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Store a new lead submission from the landing page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'zipcodes' => ['required', 'array', 'min:1'],
            'zipcodes.*' => ['required', 'exists:zipcodes,id'],
            'initial_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create the lead
        $lead = Lead::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'initial_notes' => $validated['initial_notes'] ?? null,
            'lead_status' => 'new',
            'payment_status' => 'unpaid',
        ]);

        // Attach zipcodes
        $lead->zipcodes()->attach($validated['zipcodes']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your interest! We will contact you soon.',
            ], 201);
        }

        return redirect()->back()->with('success', 'Thank you for your interest! We will contact you soon.');
    }


    /**
     * Store a lead via the JSON API (POST /api/leads).
     * Always returns JSON; validation failures are 422 with standard Laravel error shape when appropriate.
     */
    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'zipcodes' => ['required', 'array', 'min:1'],
            'zipcodes.*' => ['required', 'exists:zipcodes,id'],
            'initial_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $lead = Lead::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'initial_notes' => $validated['initial_notes'] ?? null,
            'lead_status' => 'new',
            'payment_status' => 'unpaid',
        ]);

        $lead->zipcodes()->attach($validated['zipcodes']);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your interest! We will contact you soon.',
            'data' => [
                'id' => $lead->id,
                'zipcode_ids' => $validated['zipcodes'],
            ],
        ], 201);
    }

    /**
     * Check if a zipcode is available.
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'zipcode' => ['required', 'string', 'max:10'],
        ]);

        $zipcodeCode = trim($request->zipcode);

        $zipcode = Zipcode::where('code', '=', $zipcodeCode, 'and')
            ->where('is_active', '=', true, 'and')
            ->first();

        if (!$zipcode) {
            return response()->json([
                'available' => false,
                'is_in_coverage_area' => false,
                'message' => 'ZIP code not available in our coverage area.',
            ], 200);
        }

        $isSubscribed = UserZipcodeSubscription::active()
            ->forZipcode($zipcode->id)
            ->exists();

        if ($isSubscribed) {
            return response()->json([
                'available' => false,
                'is_in_coverage_area' => true,
                'message' => "ZIP code " . $zipcodeCode . " is currently owned by another agent. Try a different ZIP code.",
            ], 200);
        }

        if (! $zipcode->hasPurchasablePlans()) {
            return response()->json([
                'available' => false,
                'is_in_coverage_area' => true,
                'message' => 'ZIP code is in our coverage area but Stripe subscription pricing is not configured yet.',
            ], 200);
        }

        $purchasablePlans = $zipcode->purchasableBillingPlans();

        // Count leads/datasets for this zipcode (same logic as home route)
        $leadsCount = \App\Models\Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcode) {
            $q->where('zipcode_id', $zipcode->id);
        })->count();

        return response()->json([
            'available' => true,
            'message' => 'ZIP code is available!',
            'zipcode' => [
                'id' => $zipcode->id,
                'code' => $zipcode->code,
                'city' => $zipcode->city ?? '',
                'state' => $zipcode->state ?? '',
                'label' => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}",
                'monthly_price' => $zipcode->monthly_price ?? 0,
                'yearly_price' => $zipcode->yearly_price ?? 0,
                'billing_plans' => $purchasablePlans,
                'default_billing_interval' => collect($purchasablePlans)->value('interval'),
                'leads_count' => $leadsCount > 0 ? $leadsCount : rand(50, 300),
            ],
        ], 200);
    }
}
