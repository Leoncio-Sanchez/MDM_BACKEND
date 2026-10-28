<?php

declare(strict_types=1);

use App\Modules\Reportes\Presentation\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

// Reportes con nombres y documentos: cada uno exige el acceso de su área (detalle fila por fila).
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:licencias'])->prefix('api/lf-itse/reportes')->group(function (): void {
    Route::get('licencias/', [ReportesController::class, 'padronLicencias'])->name('reportes.licencias');
    // Informe formal numerado (INFORME N° 001-2026-MDM/GDES); reimprimir el mismo mes conserva el número
    Route::get('licencias/pdf/', [ReportesController::class, 'informeLicenciasPdf'])->name('reportes.licencias.pdf');

    // Auditoría (OCI): licencias anuladas/revocadas y expedientes de licencia improcedentes
    Route::get('bajas/licencias/', [ReportesController::class, 'bajas'])->defaults('tipo', 'LF')->name('reportes.bajas.licencias');
    Route::get('improcedentes/licencias/', [ReportesController::class, 'improcedentes'])->defaults('tipo', 'LF')->name('reportes.improcedentes.licencias');
});

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:itse'])->prefix('api/lf-itse/reportes')->group(function (): void {
    // Auditoría (OCI): certificados ITSE anulados/revocados y expedientes ITSE improcedentes
    Route::get('bajas/itse/', [ReportesController::class, 'bajas'])->defaults('tipo', 'ITSE')->name('reportes.bajas.itse');
    Route::get('improcedentes/itse/', [ReportesController::class, 'improcedentes'])->defaults('tipo', 'ITSE')->name('reportes.improcedentes.itse');
});
