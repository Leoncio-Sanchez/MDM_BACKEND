<?php

declare(strict_types=1);
use App\Modules\NivelRiesgo\Presentation\Controllers\NivelRiesgoController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware('auth:api')->group(function () {
    Route::apiResource('niveles-riesgo', NivelRiesgoController::class);
});
