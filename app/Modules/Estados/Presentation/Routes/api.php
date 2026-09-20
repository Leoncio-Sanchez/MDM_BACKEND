<?php

declare(strict_types=1);

use App\Modules\Estados\Presentation\Controllers\EstadoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function () {
    Route::get('estados', [EstadoController::class, 'index']);
    Route::get('estados/{id}', [EstadoController::class, 'show'])->whereNumber('id');
    Route::post('estados', [EstadoController::class, 'store']);
    Route::put('estados/{id}', [EstadoController::class, 'update'])->whereNumber('id');
    Route::delete('estados/{id}', [EstadoController::class, 'destroy'])->whereNumber('id');
});
