<?php
declare(strict_types=1);

use App\Modules\ITSE\Presentation\Controllers\ItseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    // Estáticas primero
    Route::get('itse/buscar/', [ItseController::class, 'buscar']);
    Route::get('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/verificar-expediente/', [ItseController::class, 'verificarExpediente']);
    Route::post('itse/inactivar/', [ItseController::class, 'inactivar']);

    // Dinámicas después
    Route::post('itse/', [ItseController::class, 'store']);
    Route::get('itse/{pk}/', [ItseController::class, 'show'])->whereNumber('pk');
    Route::put('itse/{pk}/', [ItseController::class, 'update'])->whereNumber('pk');
    Route::post('itse/{pk}/notificacion/', [ItseController::class, 'notificacion'])->whereNumber('pk');
});
