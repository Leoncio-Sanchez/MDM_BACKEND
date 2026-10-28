<?php

declare(strict_types=1);

use App\Modules\Configuracion\Presentation\Controllers\ConfiguracionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:admin'])->prefix('api/lf-itse/configuracion')->group(function (): void {
    Route::get('informes/', [ConfiguracionController::class, 'informe'])->name('configuracion.informes');
    Route::put('informes/', [ConfiguracionController::class, 'actualizarInforme'])->name('configuracion.informes.actualizar');
});
