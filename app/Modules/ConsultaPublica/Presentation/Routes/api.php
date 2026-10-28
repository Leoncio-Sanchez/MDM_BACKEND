<?php

declare(strict_types=1);

use App\Modules\ConsultaPublica\Presentation\Controllers\ConsultaPublicaController;
use Illuminate\Support\Facades\Route;

// Sin autenticación: el ciudadano consulta con el número del trámite y su DNI/RUC.
// Limitado por IP para evitar que se prueben documentos de forma masiva.
Route::middleware(['throttle:consulta-publica'])->prefix('api/publico')->group(function (): void {
    Route::get('consulta/', [ConsultaPublicaController::class, 'consultar'])->name('publico.consulta');
});
