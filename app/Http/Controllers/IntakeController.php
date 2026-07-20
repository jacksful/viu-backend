<?php

namespace App\Http\Controllers;

use App\Models\CustomerIntake;
use App\Models\UserZipcodeSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IntakeController extends Controller
{
    public function show(Request $request, UserZipcodeSubscription $subscription): View
    {
        $this->assertIntakeAllowed($subscription);

        $subscription->loadMissing('user', 'customerIntake');
        $zipcode = $subscription->zipcodes->first();
        $user = $subscription->user;

        return view('intake.show', [
            'submitted' => $subscription->customerIntake?->isSubmitted() ?? false,
            'zipCode' => $zipcode?->code ?? '-----',
            'firstName' => filled($user?->first_name)
                ? $user->first_name
                : (explode(' ', trim($user?->name ?? ''), 2)[0] ?: 'there'),
            'defaults' => [
                'full_name' => $user?->name,
                'phone' => $user?->phone,
                'email' => $user?->email,
            ],
            'submitUrl' => \App\Support\IntakeUrl::storeForSubscription($subscription),
        ]);
    }

    public function store(Request $request, UserZipcodeSubscription $subscription): JsonResponse
    {
        $this->assertIntakeAllowed($subscription);

        $subscription->loadMissing('customerIntake');

        if ($subscription->customerIntake?->isSubmitted()) {
            return response()->json([
                'message' => 'Intake has already been submitted for this territory.',
            ], 422);
        }

        $validated = $request->validate([
            'headshot' => ['required', 'file', 'extensions:jpg,jpeg,png', 'max:15360'],
            'logo' => ['required', 'file', 'extensions:svg,ai,eps,png', 'max:15360'],
            'brokerage_logo' => ['nullable', 'file', 'extensions:svg,ai,eps,png', 'max:15360'],
            'lifestyle' => ['nullable', 'file', 'extensions:jpg,jpeg,png', 'max:15360'],
            'color1' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'color2' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'full_name' => ['required', 'string', 'max:255'],
            'tagline' => ['required', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                $words = str_word_count(trim((string) $value));

                if ($words > 8) {
                    $fail('The tagline must be 8 words or fewer.');
                }
            }],
            'bio' => ['required', 'string', 'max:5000'],
            'years' => ['required', 'integer', 'min:0', 'max:80'],
            'credential' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['required', 'url', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'booking' => ['nullable', 'url', 'max:255'],
            'brokerage' => ['required', 'string', 'max:255'],
            'brokerage_address' => ['required', 'string', 'max:500'],
            'license' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'disclaimers' => ['nullable', 'string', 'max:5000'],
            'eho' => ['accepted'],
            'confirm' => ['accepted'],
        ]);

        $zipcode = $subscription->zipcodes->first();

        if (! $zipcode) {
            return response()->json([
                'message' => 'Territory not found for this subscription.',
            ], 422);
        }

        $storageDir = "customer-intakes/{$subscription->user_id}/{$subscription->id}";

        $intake = DB::transaction(function () use ($subscription, $zipcode, $validated, $request, $storageDir): CustomerIntake {
            $paths = [
                'headshot_path' => $this->storeUpload($request->file('headshot'), $storageDir, 'headshot'),
                'logo_path' => $this->storeUpload($request->file('logo'), $storageDir, 'logo'),
                'brokerage_logo_path' => $request->file('brokerage_logo')
                    ? $this->storeUpload($request->file('brokerage_logo'), $storageDir, 'brokerage-logo')
                    : null,
                'lifestyle_photo_path' => $request->file('lifestyle')
                    ? $this->storeUpload($request->file('lifestyle'), $storageDir, 'lifestyle')
                    : null,
            ];

            return CustomerIntake::updateOrCreate(
                ['user_zipcode_subscription_id' => $subscription->id],
                [
                    'user_id' => $subscription->user_id,
                    'zipcode_id' => $zipcode->id,
                    ...$paths,
                    'brand_color_1' => filled($validated['color1'] ?? null)
                        ? $this->normalizeHex($validated['color1'])
                        : null,
                    'brand_color_2' => filled($validated['color2'] ?? null)
                        ? $this->normalizeHex($validated['color2'])
                        : null,
                    'full_name' => $validated['full_name'],
                    'tagline' => $validated['tagline'],
                    'bio' => $validated['bio'],
                    'years_in_business' => (int) $validated['years'],
                    'credential' => $validated['credential'],
                    'display_phone' => $validated['phone'],
                    'display_email' => $validated['email'],
                    'website_url' => $validated['website'],
                    'instagram' => $validated['instagram'] ?? null,
                    'booking_url' => $validated['booking'] ?? null,
                    'brokerage_name' => $validated['brokerage'],
                    'brokerage_address' => $validated['brokerage_address'],
                    'license_number' => $validated['license'],
                    'license_state' => strtoupper($validated['state']),
                    'disclaimers' => $validated['disclaimers'] ?? null,
                    'equal_housing_acknowledged' => true,
                    'confirmed' => true,
                    'submitted_at' => now(),
                ],
            );
        });

        return response()->json([
            'message' => 'Intake submitted successfully.',
            'intake_id' => $intake->id,
        ]);
    }

    protected function assertIntakeAllowed(UserZipcodeSubscription $subscription): void
    {
        if ($subscription->status !== 'active') {
            abort(403, 'This subscription is not active.');
        }

        if (empty($subscription->zipcode_ids)) {
            abort(404);
        }
    }

    protected function storeUpload(\Illuminate\Http\UploadedFile $file, string $directory, string $name): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();

        return $file->storeAs(
            $directory,
            $name.'.'.$extension,
            'public',
        );
    }

    protected function normalizeHex(string $color): string
    {
        $color = trim($color);

        if (! str_starts_with($color, '#')) {
            $color = '#'.$color;
        }

        return strtoupper($color);
    }
}
