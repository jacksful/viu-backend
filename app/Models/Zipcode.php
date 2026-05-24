<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zipcode extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'city',
        'state',
        'area',
        'monthly_price',
        'yearly_price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all uploaded zipcodes for this zipcode.
     */
    public function uploadedZipcodes()
    {
        return $this->hasMany(UploadedZipcode::class);
    }

    /**
     * Get all datasets through uploaded zipcodes.
     */
    public function datasets()
    {
        return $this->hasManyThrough(Dataset::class, UploadedZipcode::class, 'zipcode_id', 'uploaded_zipcode_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserZipcodeSubscription::class);
    }

    /**
     * Get all leads associated with this zipcode.
     */
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_zipcode')
            ->withTimestamps();
    }
}
