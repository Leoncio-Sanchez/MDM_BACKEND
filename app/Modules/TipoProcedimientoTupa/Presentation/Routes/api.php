<?php

declare(strict_types=1);
use App\Modules\TipoProcedimientoTupa\Presentation\Controllers\TipoProcedimientoTupaController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware('auth:api')->group(function () {
    Route::apiResource('tipos-procedimiento-tupa', TipoProcedimientoTupaController::class)->parameters([
        'tipos-procedimiento-tupa' => 'id',
    ]);
});
