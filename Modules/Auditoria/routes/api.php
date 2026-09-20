<?php

declare(strict_types=1);

use App\Modules\Auditoria\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('auditorias', AuditoriaController::class)->names('auditoria');
});
