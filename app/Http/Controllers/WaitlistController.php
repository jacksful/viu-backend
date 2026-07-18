<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'zip_code' => ['required', 'string', 'regex:/^\d{5}$/'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $waitlist = Waitlist::create([
            'name' => strip_tags($validated['name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'zip_code' => $validated['zip_code'],
            'message' => isset($validated['message']) ? trim($validated['message']) : null,
            'status' => 'new',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have been added to the waitlist. We will contact you if this ZIP becomes available.',
            'data' => [
                'id' => $waitlist->id,
            ],
        ], 201);
    }
}
