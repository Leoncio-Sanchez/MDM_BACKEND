<?php

declare(strict_types=1);
use App\Modules\TipoDocumentoIdentidad\Presentation\Controllers\TipoDocumentoIdentidadController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/lf-itse')->middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:*,admin'])->group(function () {
    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)->parameters([
        'tipos-documento-identidad' => 'id',
    ]);
});
