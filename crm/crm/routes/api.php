<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ContactController;

Route::prefix('v1')->group(function () {
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('contacts', ContactController::class);
});
