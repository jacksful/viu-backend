<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerAuthController extends Controller
{
  /**
   * Show the registration form
   */
  public function showRegisterForm()
  {
    return view('auth.customer.register');
  }

  /**
   * Handle customer registration
   */
  public function register(Request $request)
  {
    $request->validate([
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'name' => $request->first_name . ' ' . $request->last_name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'role' => 'customer',
      'status' => 'pending', // Set to pending until email is verified
    ]);

    event(new Registered($user));

    // Log the user in after registration
    Auth::login($user);

    // Redirect to verification notice if email is not verified
    if (!$user->hasVerifiedEmail()) {
      return redirect()->route('verification.notice')
        ->with('status', 'Registration successful! Please check your email to verify your account.');
    }

    // If email is already verified (shouldn't happen normally), redirect to dashboard
    return redirect()->route('user.dashboard')
      ->with('status', 'Registration successful!');
  }

  /**
   * Show the login form
   */
  public function showLoginForm()
  {
    return view('auth.customer.login');
  }

  /**
   * Handle customer login
   */
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
      $request->session()->regenerate();

      $user = Auth::user();

      // Ensure user is a customer
      if ($user->role !== 'customer') {
        Auth::logout();
        return back()->withErrors([
          'email' => 'You do not have access to the customer portal.',
        ]);
      }

      // Check if email is verified - redirect to verification if not verified
      if (!$user->hasVerifiedEmail()) {
        return redirect()->route('verification.notice')
          ->with('status', 'Please verify your email address to access the dashboard.');
      }

      // Update status to active if email is verified
      if ($user->status === 'pending' && $user->hasVerifiedEmail()) {
        $user->update(['status' => 'active']);
      }

      return redirect()->intended(route('user.dashboard'));
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
  }

  /**
   * Handle customer logout
   */
  public function logout(Request $request)
  {

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('user.login');
  }
}
