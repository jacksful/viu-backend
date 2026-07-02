<?php

namespace App\Cms\Support;

use App\Models\Dataset;
use App\Models\Zipcode;

class PageRenderContext
{
    /** @var list<array<string, mixed>>|null */
    private ?array $zipcodes = null;

    public static function make(): self
    {
        return new self;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function zipcodes(): array
    {
        if ($this->zipcodes !== null) {
            return $this->zipcodes;
        }

        $this->zipcodes = Zipcode::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function (Zipcode $zipcode) {
                $leadsCount = Dataset::query()
                    ->whereHas('uploadedZipcode', fn ($q) => $q->where('zipcode_id', $zipcode->id))
                    ->count();

                return [
                    'id' => $zipcode->id,
                    'code' => $zipcode->code,
                    'city' => $zipcode->city ?? '',
                    'state' => $zipcode->state ?? '',
                    'label' => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}",
                    'monthly_price' => $zipcode->monthly_price ?? 349,
                    'leads_count' => $leadsCount > 0 ? $leadsCount : rand(50, 300),
                ];
            })
            ->values()
            ->all();

        return $this->zipcodes;
    }
}
