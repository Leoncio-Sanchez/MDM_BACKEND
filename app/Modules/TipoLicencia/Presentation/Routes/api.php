<?php

declare(strict_types=1);
use App\Modules\TipoLicencia\Presentation\Controllers\TipoLicenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:*,admin'])->prefix('api/lf-itse')->group(function () {
    Route::apiResource('tipos-licencia', TipoLicenciaController::class)->parameters([
        'tipos-licencia' => 'id',
    ]);
});
