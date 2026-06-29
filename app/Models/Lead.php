<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'initial_notes',
        'lead_status',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_subscription_id',
        'last_contact_date',
        'next_follow_up_date',
        'internal_comments',
        'converted_to_user_id',
        'converted_at',
    ];

    protected $casts = [
        'last_contact_date' => 'date',
        'next_follow_up_date' => 'date',
        'converted_at' => 'datetime',
    ];

    /**
     * Get the user this lead was converted to.
     */
    public function convertedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_to_user_id');
    }

    /**
     * Get the zipcodes associated with this lead.
     */
    public function zipcodes(): BelongsToMany
    {
        return $this->belongsToMany(Zipcode::class, 'lead_zipcode')
            ->withTimestamps();
    }

    /**
     * Check if lead is ready to convert (meets all requirements).
     */
    public function isReadyToConvert(): bool
    {
        return $this->lead_status === 'interested' 
            && $this->payment_status === 'paid'
            && $this->zipcodes()->count() > 0
            && is_null($this->converted_to_user_id);
    }
}
