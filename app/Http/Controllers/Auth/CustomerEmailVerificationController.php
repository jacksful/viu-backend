<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerEmailVerificationController extends Controller
{
    /**
     * Mark the user's email as verified via signed verification link.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'customer') {
            abort(403);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('user.dashboard')->with('status', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $user->update(['status' => 'active']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('status', 'Email verified successfully!');
    }
}
