<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerIntake extends Model
{
    protected $fillable = [
        'user_id',
        'user_zipcode_subscription_id',
        'zipcode_id',
        'headshot_path',
        'logo_path',
        'brokerage_logo_path',
        'lifestyle_photo_path',
        'brand_color_1',
        'brand_color_2',
        'full_name',
        'tagline',
        'bio',
        'years_in_business',
        'credential',
        'display_phone',
        'display_email',
        'website_url',
        'instagram',
        'booking_url',
        'brokerage_name',
        'brokerage_address',
        'license_number',
        'license_state',
        'disclaimers',
        'equal_housing_acknowledged',
        'confirmed',
        'submitted_at',
    ];

    protected $casts = [
        'years_in_business' => 'integer',
        'equal_housing_acknowledged' => 'boolean',
        'confirmed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserZipcodeSubscription::class, 'user_zipcode_subscription_id');
    }

    public function zipcode(): BelongsTo
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
