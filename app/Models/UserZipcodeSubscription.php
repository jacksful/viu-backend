<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserZipcodeSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'zipcode_ids',
        'start_date',
        'end_date',
        'status',
        'stripe_subscription_id',
        'stripe_customer_id',
        'billing_interval',
    ];

    protected $casts = [
        'zipcode_ids' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Send notification when a new subscription is created with zipcodes
        static::created(function (UserZipcodeSubscription $subscription) {
            if ($subscription->zipcode_ids && !empty($subscription->zipcode_ids)) {
                $subscription->sendZipcodeAssignmentNotification();
            }
        });

        // Send notification when zipcodes are updated
        static::updated(function (UserZipcodeSubscription $subscription) {
            // Check if zipcode_ids were changed
            if ($subscription->wasChanged('zipcode_ids')) {
                $oldZipcodes = $subscription->getOriginal('zipcode_ids') ?? [];
                $newZipcodes = $subscription->zipcode_ids ?? [];
                
                // Only send notification if new zipcodes were added
                if (!empty($newZipcodes) && $newZipcodes !== $oldZipcodes) {
                    $subscription->sendZipcodeAssignmentNotification();
                }
            }
            
            // Send notification when status changes to active
            if ($subscription->wasChanged('status') && $subscription->status === 'active') {
                $subscription->sendSubscriptionActivatedNotification();
            }
        });
    }

    /**
     * RELATIONSHIPS
     */

    // A subscription belongs to a specific customer user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ACCESSORS & MUTATORS
     */

    // Get zipcodes collection from zipcode_ids array
    public function getZipcodesAttribute()
    {
        if (empty($this->zipcode_ids)) {
            return collect();
        }

        return Zipcode::whereIn('id', $this->zipcode_ids)->get();
    }

    /**
     * SCOPES
     */

    // Only active subscriptions
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Only expired subscriptions
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Ensure the logged in user is customer
    public function scopeOnlyCustomers($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('role', 'customer');
        });
    }

    // Filter by zipcode ID (searches within JSON array)
    public function scopeForZipcode($query, $zipcodeId)
    {
        return $query->whereJsonContains('zipcode_ids', $zipcodeId);
    }

    public function formattedStartDate(): string
    {
        return $this->start_date?->format('M j, Y') ?? '—';
    }

    public function formattedEndDate(): string
    {
        return $this->end_date?->format('M j, Y') ?? 'Ongoing';
    }

    public function revenueEndAt(): Carbon
    {
        if ($this->status === 'active') {
            return now();
        }

        return $this->end_date ? Carbon::parse($this->end_date) : now();
    }

    /**
     * NOTIFICATION METHODS
     */

    /**
     * Send notification when zipcodes are assigned to customer
     */
    protected function sendZipcodeAssignmentNotification(): void
    {
        if (!$this->user || $this->user->role !== 'customer') {
            return;
        }

        $zipcodes = $this->zipcodes;
        
        if ($zipcodes->isEmpty()) {
            return;
        }

        // Format zipcode list for notification
        $zipcodeList = $this->formatZipcodeList($zipcodes);
        
        // Create notification
        Notification::create([
            'user_id' => $this->user_id,
            'type' => 'zipcode_assigned',
            'title' => 'ZIP Codes Assigned',
            'description' => "You have been assigned access to the following ZIP codes: {$zipcodeList}",
            'icon' => 'fas fa-map-marker-alt',
            'icon_color' => 'text-blue-600',
            'is_read' => false,
            'data' => [
                'zipcode_ids' => $this->zipcode_ids,
                'subscription_id' => $this->id,
            ],
        ]);
    }

    /**
     * Send notification when subscription is activated
     */
    protected function sendSubscriptionActivatedNotification(): void
    {
        if (!$this->user || $this->user->role !== 'customer') {
            return;
        }

        $zipcodes = $this->zipcodes;
        
        if ($zipcodes->isEmpty()) {
            return;
        }

        $zipcodeList = $this->formatZipcodeList($zipcodes);
        
        Notification::create([
            'user_id' => $this->user_id,
            'type' => 'subscription_activated',
            'title' => 'Subscription Activated',
            'description' => "Your subscription for the following ZIP codes has been activated: {$zipcodeList}",
            'icon' => 'fas fa-check-circle',
            'icon_color' => 'text-green-600',
            'is_read' => false,
            'data' => [
                'zipcode_ids' => $this->zipcode_ids,
                'subscription_id' => $this->id,
                'status' => $this->status,
            ],
        ]);
    }

    /**
     * Format zipcode list for notification description
     */
    protected function formatZipcodeList($zipcodes): string
    {
        if ($zipcodes->isEmpty()) {
            return '';
        }

        $formatted = $zipcodes->map(function ($zipcode) {
            $parts = ["ZIP {$zipcode->code}"];
            if ($zipcode->city) {
                $parts[] = $zipcode->city;
            }
            if ($zipcode->state) {
                $parts[] = $zipcode->state;
            }
            return implode(', ', $parts);
        })->join(', ');

        // Limit to first 3 zipcodes if more than 3
        if ($zipcodes->count() > 3) {
            $remaining = $zipcodes->count() - 3;
            $firstThree = $zipcodes->take(3)->map(function ($zipcode) {
                return "ZIP {$zipcode->code}";
            })->join(', ');
            return "{$firstThree}, and {$remaining} more";
        }

        return $formatted;
    }
}
