<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public CMS API cache TTL
    |--------------------------------------------------------------------------
    |
    | Seconds to remember the aggregated /api/cms payload. Cache is also
    | cleared whenever a CMS section model is saved in the admin.
    |
    */

    'api_cache_ttl' => max(1, (int) env('CMS_API_CACHE_TTL', 3600)),

];
