<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

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
            return $this->redirectAfterVerification($user);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $user->update(['status' => 'active']);
        }

        return $this->redirectAfterVerification($user->fresh());
    }

    protected function redirectAfterVerification(User $user): RedirectResponse
    {
        if ($user->password_set_at) {
            return redirect()->route('user.login')
                ->with('status', 'Email verified successfully! Please sign in.');
        }

        $setupUrl = URL::temporarySignedRoute(
            'password.setup',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        return redirect()->to($setupUrl)
            ->with('status', 'Email verified! Please set your password to continue.');
    }
}
