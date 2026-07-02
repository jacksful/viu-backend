<?php

namespace App\Support;

use App\Models\UserZipcodeSubscription;
use Illuminate\Support\Facades\URL;

class IntakeUrl
{
    public static function forSubscription(UserZipcodeSubscription $subscription): string
    {
        return URL::temporarySignedRoute(
            'intake.show',
            now()->addDays(90),
            ['subscription' => $subscription->id],
        );
    }

    public static function storeForSubscription(UserZipcodeSubscription $subscription): string
    {
        return URL::temporarySignedRoute(
            'intake.store',
            now()->addDays(90),
            ['subscription' => $subscription->id],
        );
    }
}
