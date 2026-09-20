<?php

declare(strict_types=1);
use App\Modules\Giros\Presentation\Controllers\GiroController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware('api')->group(function () {
    Route::apiResource('giros', GiroController::class);
});
