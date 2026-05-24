<?php

use App\Http\Controllers\Api\CmsPublicController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/cms', CmsPublicController::class)->name('api.cms');

Route::post('/interested-people', [ContactController::class, 'storeApi'])
    ->name('api.interested-people.store');

Route::post('/leads/check-availability', [LeadController::class, 'checkAvailability'])
    ->name('api.leads.check-availability');

Route::post('/leads', [LeadController::class, 'storeApi'])
    ->name('api.leads.store');
