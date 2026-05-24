<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerVerificationNoticeController extends Controller
{
  /**
   * Display the email verification notice.
   */
  public function __invoke(Request $request)
  {
    // If user is not authenticated, redirect to login
    if (!Auth::check()) {
      return redirect()->route('user.login')
        ->with('status', 'Please log in to verify your email.');
    }

    // If already verified, redirect to dashboard
    if ($request->user()->hasVerifiedEmail()) {
      return redirect()->route('user.dashboard');
    }

    return view('auth.customer.verify-email');
  }
}
