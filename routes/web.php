<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\CustomerEmailVerificationController;
use App\Http\Controllers\Auth\CustomerVerificationNoticeController;
use App\Http\Controllers\Auth\CustomerVerificationResendController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\FeedbackController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\PasswordController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\EnsureCustomerRole;
use App\Models\CmsAboutHeroSection;
use App\Models\CmsAboutMissionSection;
use App\Models\CmsAboutPrinciplesSection;
use App\Models\CmsHeroSection;
use App\Models\CmsPricingSection;
use App\Models\CmsQaSection;
use App\Models\CmsRecognitionSection;
use App\Models\CmsStrategicWindowSection;
use App\Models\CmsTerritoryZipSection;
use App\Models\Dataset;
use App\Models\Zipcode;
use Illuminate\Support\Facades\Route;

if (config('cms.use_page_builder')) {
    Route::get('/', [PageController::class, 'home'])->name('home');
    Route::get('/preview/pages/{page}', [PageController::class, 'preview'])->name('pages.preview');
} else {
    Route::get('/', function () {
        $zipcodes = Zipcode::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($zipcode) {
                $leadsCount = Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcode) {
                    $q->where('zipcode_id', $zipcode->id);
                })->count();

                return [
                    'id' => $zipcode->id,
                    'code' => $zipcode->code,
                    'city' => $zipcode->city ?? '',
                    'state' => $zipcode->state ?? '',
                    'label' => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}",
                    'monthly_price' => $zipcode->monthly_price ?? 349,
                    'leads_count' => $leadsCount > 0 ? $leadsCount : rand(50, 300),
                ];
            });

        $hero = CmsHeroSection::singleton();
        $strategicWindow = CmsStrategicWindowSection::singleton();
        $territoryZip = CmsTerritoryZipSection::singleton();
        $recognition = CmsRecognitionSection::singleton();
        $pricing = CmsPricingSection::singleton();
        $qa = CmsQaSection::singleton();

        return view('home', compact('zipcodes', 'hero', 'strategicWindow', 'territoryZip', 'recognition', 'pricing', 'qa'));
    })->name('home');
}

if (! config('cms.use_page_builder')) {
    Route::view('/privacy', 'privacy')->name('privacy');
    Route::view('/terms', 'terms')->name('terms');
}

if (! config('cms.use_page_builder')) {
    Route::get('/about', function () {
        $aboutHero = CmsAboutHeroSection::singleton();
        $aboutMission = CmsAboutMissionSection::singleton();
        $aboutPrinciples = CmsAboutPrinciplesSection::singleton();

        return view('about', compact('aboutHero', 'aboutMission', 'aboutPrinciples'));
    })->name('about');
}

// Lead submission route (public)
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::post('/leads/check-availability', [LeadController::class, 'checkAvailability'])->name('leads.check-availability');

// Stripe checkout & webhooks
Route::post('/stripe/checkout', [StripeCheckoutController::class, 'create'])->name('stripe.checkout');
Route::get('/stripe/checkout/success', [StripeCheckoutController::class, 'success'])->name('stripe.checkout.success');
Route::get('/stripe/checkout/cancel', [StripeCheckoutController::class, 'cancel'])->name('stripe.checkout.cancel');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::middleware('signed')->prefix('intake')->name('intake.')->group(function () {
    Route::get('{subscription}', [IntakeController::class, 'show'])->name('show');
    Route::post('{subscription}', [IntakeController::class, 'store'])->name('store');
});

// Contact form submission route (public)
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');

// ZIP waitlist form submission route (public)
Route::post('/waitlists', [WaitlistController::class, 'store'])->name('waitlists.store');

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
            Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
            Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription');
            Route::get('/subscription/data', [SubscriptionController::class, 'getData'])->name('subscription.data');
            Route::post('/subscription/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
            Route::post('/subscription/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
            Route::post('/subscription/{subscription}/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

            // Notification routes
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

            // Feedback routes
            Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
        });
    });

    // Signed link proves identity — no auth required (users often click from email while logged out).
    Route::get('/email/verify/{id}/{hash}', CustomerEmailVerificationController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::middleware(['auth', EnsureCustomerRole::class])->group(function () {
        Route::get('/email/verify', CustomerVerificationNoticeController::class)
            ->name('verification.notice');
        Route::post('/email/verification-notification', CustomerVerificationResendController::class)
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});

if (config('cms.use_page_builder')) {
    Route::get('/about', [PageController::class, 'show'])
        ->defaults('slug', 'about')
        ->name('about');

    Route::get('/privacy', [PageController::class, 'show'])
        ->defaults('slug', 'privacy')
        ->name('privacy');

    Route::get('/terms', [PageController::class, 'show'])
        ->defaults('slug', 'terms')
        ->name('terms');

    $reserved = implode('|', config('cms.reserved_slugs', []));

    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', '^(?!'.$reserved.')[a-z0-9\-]+$')
        ->name('pages.show');
}
