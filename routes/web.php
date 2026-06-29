<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\CustomerEmailVerificationController;
use App\Http\Controllers\Auth\CustomerVerificationNoticeController;
use App\Http\Controllers\Auth\CustomerVerificationResendController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\EnsureCustomerRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $zipcodes = \App\Models\Zipcode::where('is_active', true)
        ->orderBy('code')
        ->get()
        ->map(function ($zipcode) {
            // Count leads/datasets for this zipcode
            $leadsCount = \App\Models\Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcode) {
                $q->where('zipcode_id', $zipcode->id);
            })->count();

            return [
                'id' => $zipcode->id,
                'code' => $zipcode->code,
                'city' => $zipcode->city ?? '',
                'state' => $zipcode->state ?? '',
                'label' => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}",
                'monthly_price' => $zipcode->monthly_price ?? 349,
                'leads_count' => $leadsCount > 0 ? $leadsCount : rand(50, 300), // Use actual count or placeholder
            ];
        });

    $hero = \App\Models\CmsHeroSection::singleton();
    $strategicWindow = \App\Models\CmsStrategicWindowSection::singleton();
    $territoryZip = \App\Models\CmsTerritoryZipSection::singleton();
    $recognition = \App\Models\CmsRecognitionSection::singleton();
    $pricing = \App\Models\CmsPricingSection::singleton();
    $qa = \App\Models\CmsQaSection::singleton();

    return view('home', compact('zipcodes', 'hero', 'strategicWindow', 'territoryZip', 'recognition', 'pricing', 'qa'));
});

Route::get('/about', function () {
    $aboutHero = \App\Models\CmsAboutHeroSection::singleton();
    $aboutMission = \App\Models\CmsAboutMissionSection::singleton();
    $aboutPrinciples = \App\Models\CmsAboutPrinciplesSection::singleton();

    return view('about', compact('aboutHero', 'aboutMission', 'aboutPrinciples'));
})->name('about');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

// Lead submission route (public)
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::post('/leads/check-availability', [LeadController::class, 'checkAvailability'])->name('leads.check-availability');

// Stripe checkout & webhooks
Route::post('/stripe/checkout', [StripeCheckoutController::class, 'create'])->name('stripe.checkout');
Route::get('/stripe/checkout/success', [StripeCheckoutController::class, 'success'])->name('stripe.checkout.success');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// Contact form submission route (public)
Route::post('/contacts', [\App\Http\Controllers\ContactController::class, 'store'])->name('contacts.store');

// User-facing auth: URL prefix `user/*`; `user.*` route names only inside the inner group so `verification.*` matches Laravel defaults.
Route::prefix('user')->group(function () {
    Route::name('user.')->group(function () {
        // Guest routes
        Route::middleware('guest')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [CustomerAuthController::class, 'login']);
            Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
            Route::post('/register', [CustomerAuthController::class, 'register']);
        });

        // Authenticated portal routes (require verified email, customer role)
        Route::middleware(['auth', EnsureCustomerRole::class, 'verified'])->group(function () {
            Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/export', [DashboardController::class, 'export'])->name('export');

            // Profile routes
            Route::get('/profile/edit', [\App\Http\Controllers\Customer\ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'update'])->name('profile.update');
            Route::get('/subscription', [\App\Http\Controllers\Customer\SubscriptionController::class, 'index'])->name('subscription');
            Route::get('/subscription/data', [\App\Http\Controllers\Customer\SubscriptionController::class, 'getData'])->name('subscription.data');
            Route::get('/settings', [\App\Http\Controllers\Customer\SettingsController::class, 'index'])->name('settings');

            // Notification routes
            Route::get('/notifications', [\App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [\App\Http\Controllers\Customer\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

            // Feedback routes
            Route::post('/feedback', [\App\Http\Controllers\Customer\FeedbackController::class, 'store'])->name('feedback.store');
        });
    });

    Route::middleware(['auth', EnsureCustomerRole::class])->group(function () {
        Route::get('/email/verify', CustomerVerificationNoticeController::class)
            ->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', CustomerEmailVerificationController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('/email/verification-notification', CustomerVerificationResendController::class)
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});
