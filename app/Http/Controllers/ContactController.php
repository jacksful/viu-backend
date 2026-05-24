<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Store a new contact form submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'zip_of_interest' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'zip_of_interest' => $validated['zip_of_interest'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? '',
            'status' => 'new',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us! We will get back to you soon.',
            ], 201);
        }

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }

    /**
     * Store a marketing / lead form submission via JSON API (POST /api/interested-people).
     */
    public function storeApi(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'zip_of_interest' => ['nullable', 'string', 'max:20'],
                'message' => ['nullable', 'string', 'max:5000'],
            ]);

            $contact = Contact::create([
                'name' => strip_tags($validated['name']),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'zip_of_interest' => isset($validated['zip_of_interest']) ? strip_tags($validated['zip_of_interest']) : null,
                'subject' => null,
                'message' => isset($validated['message']) ? trim($validated['message']) : '',
                'status' => 'new',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your interest! We will get back to you soon.',
                'data' => [
                    'id' => $contact->id,
                ],
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
            ], 500);
        }
    }
}
