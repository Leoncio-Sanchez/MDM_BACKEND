<?php

declare(strict_types=1);

use App\Modules\UnidadesOrganicas\Presentation\Controllers\UnidadOrganicaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function (): void {
    Route::get('unidades-organicas', [UnidadOrganicaController::class, 'index']);
    Route::get('unidades-organicas/', [UnidadOrganicaController::class, 'index']);
});
