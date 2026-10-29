<?php

declare(strict_types=1);

use App\Modules\ConsultaPublica\Presentation\Controllers\ConsultaPublicaController;
use App\Modules\ConsultaPublica\Presentation\Controllers\CuentaCiudadanoController;
use Illuminate\Support\Facades\Route;

// Sin autenticación: el ciudadano consulta con el número del trámite y su DNI/RUC.
// Limitado por IP para evitar que se prueben documentos de forma masiva.
Route::middleware(['throttle:consulta-publica'])->prefix('api/publico')->group(function (): void {
    Route::get('consulta/', [ConsultaPublicaController::class, 'consultar'])->name('publico.consulta');
});

// Cuenta del ciudadano: crearla, restablecer la contraseña e ingresar con su DNI/RUC.
// Limitado por documento e IP: no se pueden probar expedientes o contraseñas en masa.
Route::middleware(['throttle:cuenta-ciudadano'])->prefix('api/publico/cuenta')->group(function (): void {
    Route::post('registro/', [CuentaCiudadanoController::class, 'registrar'])->name('publico.cuenta.registro');
    Route::post('restablecer/', [CuentaCiudadanoController::class, 'restablecer'])->name('publico.cuenta.restablecer');
    Route::post('ingresar/', [CuentaCiudadanoController::class, 'ingresar'])->name('publico.cuenta.ingresar');
});

// Con la sesión del ciudadano: solo lo que está a nombre de su documento
Route::middleware(['auth:api', 'rol:CIUDADANO'])->prefix('api/ciudadano')->group(function (): void {
    Route::get('mis-tramites/', [CuentaCiudadanoController::class, 'misTramites'])->name('ciudadano.mis-tramites');
});
