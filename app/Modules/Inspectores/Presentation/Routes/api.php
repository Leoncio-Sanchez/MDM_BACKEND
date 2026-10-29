<?php

declare(strict_types=1);

use App\Modules\Inspectores\Presentation\Controllers\InspectorController;
use Illuminate\Support\Facades\Route;

// Consultar: acceso ITSE. Crear, editar y desactivar cuentas de inspectores: jefe de ITSE.
// (Administración también puede marcar a un usuario como inspector desde Usuarios.)
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse,jefe_itse'])->prefix('api/lf-itse')->group(function (): void {
    Route::get('inspectores/buscar', [InspectorController::class, 'buscar']);
    Route::get('inspectores', [InspectorController::class, 'index']);
    Route::post('inspectores', [InspectorController::class, 'store']);
    Route::get('inspectores/{pk}', [InspectorController::class, 'show'])->whereNumber('pk');
    Route::put('inspectores/{pk}', [InspectorController::class, 'update'])->whereNumber('pk');
    Route::delete('inspectores/{pk}', [InspectorController::class, 'destroy'])->whereNumber('pk');
});
