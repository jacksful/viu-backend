<?php

namespace App\Observers;

use App\Support\CmsPublicApiPayload;
use Illuminate\Database\Eloquent\Model;

class InvalidateCmsApiCache
{
    public function saved(Model $model): void
    {
        CmsPublicApiPayload::invalidate();
    }
}
