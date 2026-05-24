<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetFactory> */
    use HasFactory;

    protected $fillable = [
        'uploaded_zipcode_id',
        'propertyid',
        'restype',
        'tax_value',
        'address',
        'times_sold',
        'day_since_sold',
        'last_date_sold',
        'township',
        'style',
        'yearbuilt',
        'extwallfinish_desc',
        'rooftype_desc',
        'roofmaterial_desc',
        'basement_desc',
        'hctype',
        'hcfueltype_desc',
        'hcsystemtype_desc',
        'bedrooms',
        'fullbaths',
        'sfla',
        'phycondition',
        'utility',
        'propdesirability',
        'locdesirability',
        'status',
        'predicted_status',
        'correct_status',
        'status_probability',
    ];

    /**
     * Get the uploaded zipcode that owns this dataset.
     */
    public function uploadedZipcode()
    {
        return $this->belongsTo(UploadedZipcode::class);
    }

    /**
     * Get the zipcode through the uploaded zipcode relationship.
     * Access via: $dataset->uploadedZipcode->zipcode
     * This method provides direct access for convenience.
     */
    public function getZipcodeAttribute()
    {
        return $this->uploadedZipcode?->zipcode;
    }
}
