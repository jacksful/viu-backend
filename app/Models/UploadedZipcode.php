<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedZipcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_id',
        'month',
        'year',
        'csv_file',
        'status',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Send notifications when status changes to 'published'
        static::updated(function (UploadedZipcode $uploadedZipcode) {
            if ($uploadedZipcode->wasChanged('status') && $uploadedZipcode->status === 'published') {
                // Only send if there are datasets
                if ($uploadedZipcode->datasets()->count() > 0) {
                    $uploadedZipcode->notifySubscribedUsers();
                }
            }
        });
    }

    /**
     * Get the zipcode that owns this uploaded zipcode.
     */
    public function zipcode()
    {
        return $this->belongsTo(Zipcode::class);
    }

    /**
     * Get all datasets for this uploaded zipcode.
     */
    public function datasets()
    {
        return $this->hasMany(Dataset::class);
    }

    /**
     * Send notifications to users subscribed to this zipcode when dataset is published
     */
    public function notifySubscribedUsers(): void
    {
        if (!$this->zipcode_id) {
            return;
        }

        // Only send notifications if status is 'published'
        if ($this->status !== 'published') {
            return;
        }

        // Get month name
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $monthName = $months[$this->month] ?? $this->month;
        
        // Get zipcode code
        $zipcodeCode = $this->zipcode?->code ?? 'N/A';
        
        // Find all active subscriptions that include this zipcode
        $subscriptions = UserZipcodeSubscription::where('status', 'active')
            ->whereJsonContains('zipcode_ids', $this->zipcode_id)
            ->with('user')
            ->get();

        // Get dataset count
        $datasetCount = $this->datasets()->count();

        foreach ($subscriptions as $subscription) {
            if (!$subscription->user || $subscription->user->role !== 'customer') {
                continue;
            }

            // Create notification for each subscribed user
            Notification::create([
                'user_id' => $subscription->user_id,
                'type' => 'dataset_published',
                'title' => 'New Dataset Published',
                'description' => "{$monthName} {$this->year} dataset for ZIP {$zipcodeCode} is now available" . ($datasetCount > 0 ? " ({$datasetCount} properties)" : ''),
                'icon' => 'fas fa-database',
                'icon_color' => 'text-blue-600',
                'is_read' => false,
                'data' => [
                    'zipcode_id' => $this->zipcode_id,
                    'uploaded_zipcode_id' => $this->id,
                    'month' => $this->month,
                    'year' => $this->year,
                    'dataset_count' => $datasetCount,
                ],
            ]);
        }
    }
}
