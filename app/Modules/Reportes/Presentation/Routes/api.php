<?php

declare(strict_types=1);

use App\Modules\Reportes\Presentation\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

// Padrón con nombres y documentos: solo quien tiene el acceso de Licencias (detalle fila por fila).
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:licencias'])->prefix('api/lf-itse/reportes')->group(function (): void {
    Route::get('licencias/', [ReportesController::class, 'padronLicencias'])->name('reportes.licencias');
    // Informe formal numerado (INFORME N° 001-2026-MDM/GDES); reimprimir el mismo mes conserva el número
    Route::get('licencias/pdf/', [ReportesController::class, 'informeLicenciasPdf'])->name('reportes.licencias.pdf');
});
