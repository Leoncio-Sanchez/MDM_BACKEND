<?php

declare(strict_types=1);

use App\Modules\Inspectores\Presentation\Controllers\InspectorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse'])->prefix('api/lf-itse')->group(function (): void {
    Route::get('inspectores/buscar', [InspectorController::class, 'buscar']);
    Route::get('inspectores', [InspectorController::class, 'index']);
    Route::post('inspectores', [InspectorController::class, 'store']);
    Route::get('inspectores/{pk}', [InspectorController::class, 'show'])->whereNumber('pk');
    Route::put('inspectores/{pk}', [InspectorController::class, 'update'])->whereNumber('pk');
    Route::delete('inspectores/{pk}', [InspectorController::class, 'destroy'])->whereNumber('pk');
});
