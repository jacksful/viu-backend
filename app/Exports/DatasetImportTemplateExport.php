<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Sample CSV template for Market → Datasets import.
 *
 * Column names must match the importer (lowercase, underscores for spaces).
 */
class DatasetImportTemplateExport implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
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
            ],
            [
                '12345678',
                'Single Family',
                '285000',
                '123 Main St',
                '2',
                '450',
                '2024-03-15',
                'Springfield',
                'Colonial',
                '1998',
                'Brick',
                'Gable',
                'Asphalt Shingle',
                'Full',
                'Central',
                'Gas',
                'Forced Air',
                '3',
                '2',
                '1850',
                'Good',
                'Public',
                'Average',
                'Above Average',
                'Active',
                'Likely Seller',
                'Seller',
                '0.87',
            ],
            [
                '87654321',
                'Condo',
                '195000',
                '456 Oak Ave Unit 2B',
                '1',
                '820',
                '2023-01-10',
                'Riverside',
                'Ranch',
                '2005',
                'Vinyl',
                'Flat',
                'Rubber',
                'None',
                'Central',
                'Electric',
                'Heat Pump',
                '2',
                '1',
                '1100',
                'Average',
                'Public',
                'Below Average',
                'Average',
                'Active',
                'Hold',
                'Hold',
                '0.62',
            ],
        ];
    }
}
