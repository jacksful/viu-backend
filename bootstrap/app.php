<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('user.login'));

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Set default login route to user.login
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // Default redirect to user portal login
            return redirect()->guest(route('user.login'));
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('subscriptions:send-renewal-reminders')->dailyAt('09:00');
        $schedule->command('subscriptions:send-final-notices')->dailyAt('09:00');
        $schedule->command('subscriptions:send-payment-reminders')->dailyAt('09:00');
        $schedule->command('subscriptions:send-card-expiring-notices')->dailyAt('09:00');
        $schedule->command('subscriptions:send-zip-available-inquiry-notices')->dailyAt('09:00');
        $schedule->command('checkout-holds:expire')->hourly();
        $schedule->command('subscriptions:send-intake-reminders')->dailyAt('09:00');
    })
    ->create();
