<?php

declare(strict_types=1);
use App\Modules\NivelRiesgo\Presentation\Controllers\NivelRiesgoController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:*,admin'])->group(function () {
    Route::apiResource('niveles-riesgo', NivelRiesgoController::class);
});
