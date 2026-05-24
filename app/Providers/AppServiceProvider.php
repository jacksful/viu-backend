<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\EmailSetting;
use App\Models\CmsHeroSection;
use App\Models\CmsPricingSection;
use App\Models\CmsQaSection;
use App\Models\CmsRecognitionSection;
use App\Models\CmsStrategicWindowSection;
use App\Models\CmsTerritoryZipSection;
use App\Observers\ContactObserver;
use App\Observers\InvalidateCmsApiCache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            EmailSetting::applyMailConfig();
        } catch (\Throwable) {
            // Database may be unavailable during initial install.
        }

        Queue::before(function (): void {
            try {
                EmailSetting::applyMailConfig();
            } catch (\Throwable) {
                //
            }
        });

        $observer = new InvalidateCmsApiCache;

        CmsHeroSection::observe($observer);
        CmsStrategicWindowSection::observe($observer);
        CmsTerritoryZipSection::observe($observer);
        CmsRecognitionSection::observe($observer);
        CmsPricingSection::observe($observer);
        CmsQaSection::observe($observer);

        Contact::observe(ContactObserver::class);
    }
}
