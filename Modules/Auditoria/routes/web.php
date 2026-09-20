<?php

declare(strict_types=1);

use App\Modules\Auditoria\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('auditorias', AuditoriaController::class)->names('auditoria');
});
