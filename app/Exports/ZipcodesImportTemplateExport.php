<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Blank template plus example rows for Settings → Zipcodes import.
 *
 * Columns: zipcode, city, state, area, monthly_price, yearly_price, is_active
 */
class ZipcodesImportTemplateExport implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['zipcode', 'city', 'state', 'area', 'monthly_price', 'yearly_price', 'is_active'],
            ['90210', 'Beverly Hills', 'CA', 'West LA', '99', '999', 'yes'],
            ['77002', 'Houston', 'TX', 'Downtown', '49.99', '499.99', '1'],
        ];
    }
}
