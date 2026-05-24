<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if (Auth::user()->role !== 'customer') {
            Auth::logout();
            return redirect()->route('user.login')
                ->withErrors(['email' => 'You do not have access to the customer portal.']);
        }

        return $next($request);
    }
}
