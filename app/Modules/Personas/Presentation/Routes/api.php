<?php

declare(strict_types=1);

use App\Modules\Personas\Presentation\Controllers\PersonaController;
use App\Modules\Personas\Presentation\Controllers\ReniecController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    Route::get('personas/', [PersonaController::class, 'index']);
    Route::post('personas/', [PersonaController::class, 'store']);

    // IMPORTANT: Custom endpoints before generic `{pk}`
    Route::get('personas/buscar/', [PersonaController::class, 'buscar']);
    Route::get('personas/sexos/', [PersonaController::class, 'sexos']);

    Route::get('personas/{pk}/', [PersonaController::class, 'show'])->whereNumber('pk');
    Route::put('personas/{pk}/', [PersonaController::class, 'update'])->whereNumber('pk');
    Route::delete('personas/{pk}/', [PersonaController::class, 'destroy'])->whereNumber('pk');

    Route::get('personas/{pk}/documentos/', [PersonaController::class, 'documentos'])->whereNumber('pk');

    Route::get('reniec/consultar/', [ReniecController::class, 'consultar']);
});
