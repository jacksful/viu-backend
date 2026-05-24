<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CustomerVerificationResendController extends Controller
{
  /**
   * Resend the email verification notification.
   */
  public function __invoke(Request $request): RedirectResponse
  {
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->route('user.dashboard');
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'Verification link sent! Please check your email.');
  }
}
