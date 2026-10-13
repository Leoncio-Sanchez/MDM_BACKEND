<?php

declare(strict_types=1);

use App\Modules\UnidadesOrganicas\Presentation\Controllers\UnidadOrganicaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD', 'permiso:*,admin'])->prefix('api/lf-itse')->group(function (): void {
    Route::get('unidades-organicas', [UnidadOrganicaController::class, 'index']);
    Route::get('unidades-organicas/', [UnidadOrganicaController::class, 'index']);
    Route::post('unidades-organicas', [UnidadOrganicaController::class, 'store']);
    Route::get('unidades-organicas/{id}', [UnidadOrganicaController::class, 'show'])->whereNumber('id');
    Route::put('unidades-organicas/{id}', [UnidadOrganicaController::class, 'update'])->whereNumber('id');
    Route::delete('unidades-organicas/{id}', [UnidadOrganicaController::class, 'destroy'])->whereNumber('id');
});
