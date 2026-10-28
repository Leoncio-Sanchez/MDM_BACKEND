<?php

declare(strict_types=1);

use App\Modules\Dashboard\Presentation\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Solo cifras agregadas: las ve todo el personal, sin exigir el acceso a cada módulo.
Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD'])->prefix('api/lf-itse')->group(function (): void {
    Route::get('dashboard/resumen/', [DashboardController::class, 'resumen'])->name('dashboard.resumen');
});
