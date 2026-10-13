<?php

declare(strict_types=1);
use App\Modules\Zonificacion\Presentation\Controllers\ZonificacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD'])->group(function () {
    Route::apiResource('zonificaciones', ZonificacionController::class);
});
