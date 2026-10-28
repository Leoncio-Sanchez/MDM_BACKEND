<?php

declare(strict_types=1);

use App\Modules\Reportes\Presentation\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

// Padrón con nombres y documentos: solo quien tiene el acceso de Licencias (detalle fila por fila).
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:licencias'])->prefix('api/lf-itse/reportes')->group(function (): void {
    Route::get('licencias/', [ReportesController::class, 'padronLicencias'])->name('reportes.licencias');
});
