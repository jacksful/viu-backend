<?php

namespace App\Support;

use App\Models\CloudflareSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareTurnstile
{
    public static function verify(?string $token, ?string $remoteIp = null): bool
    {
        $settings = CloudflareSetting::singleton();

        if (! $settings->isConfigured()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                    'secret' => $settings->secret_key,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]));

            if (! $response->successful()) {
                Log::warning('Cloudflare Turnstile verification request failed.', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            return $response->json('success') === true;
        } catch (\Throwable $exception) {
            Log::warning('Cloudflare Turnstile verification error.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
