<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class CustomerEmailVerificationController extends Controller
{
  /**
   * Mark the authenticated user's email address as verified.
   */
  public function __invoke(EmailVerificationRequest $request): RedirectResponse
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->route('user.dashboard')->with('status', 'Email already verified.');
    }

    if ($request->user()->markEmailAsVerified()) {
      event(new Verified($request->user()));

      // Update user status to active
      $request->user()->update(['status' => 'active']);
    }

    return redirect()->route('user.dashboard')->with('status', 'Email verified successfully!');
  }
}
