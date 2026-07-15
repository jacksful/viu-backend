<?php

namespace App\Services;

use App\Support\ZipcodeValidationSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZipcodeValidationService
{
    public function isValidUsZipcode(string $zipcode): bool
    {
        $baseUrl = ZipcodeValidationSettings::apiBaseUrl();

        if ($baseUrl === '') {
            return true;
        }

        $normalizedZip = preg_replace('/\D/', '', $zipcode) ?? '';

        if ($normalizedZip === '') {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get($baseUrl.$normalizedZip);

            if (! $response->successful()) {
                return false;
            }

            $data = $response->json();

            return is_array($data)
                && ! empty($data)
                && ! empty($data['places']);
        } catch (\Throwable $exception) {
            Log::warning('Zipcode validation API request failed.', [
                'zipcode' => $normalizedZip,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
