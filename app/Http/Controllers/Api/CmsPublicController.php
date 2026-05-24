<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CmsPublicApiPayload;
use Illuminate\Http\JsonResponse;

class CmsPublicController extends Controller
{
    /**
     * Public aggregate of all marketing CMS sections (single round trip).
     */
    public function __invoke(): JsonResponse
    {
        $payload = CmsPublicApiPayload::build();

        return response()->json($payload);
    }
}
