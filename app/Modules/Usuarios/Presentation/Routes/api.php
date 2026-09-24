<?php

declare(strict_types=1);

use App\Modules\Usuarios\Presentation\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('api/lf-itse')->group(function (): void {
    Route::get('usuarios/menus', [UsuarioController::class, 'menus']);
    Route::get('usuarios', [UsuarioController::class, 'index']);
    Route::post('usuarios', [UsuarioController::class, 'store']);
    Route::get('usuarios/{pk}/detalle', [UsuarioController::class, 'show'])->whereNumber('pk');
    Route::patch('usuarios/{pk}/cambiar-password', [UsuarioController::class, 'cambiarPassword'])->whereNumber('pk');
    Route::get('usuarios/{pk}', [UsuarioController::class, 'show'])->whereNumber('pk');
    Route::put('usuarios/{pk}', [UsuarioController::class, 'update'])->whereNumber('pk');
    Route::delete('usuarios/{pk}', [UsuarioController::class, 'destroy'])->whereNumber('pk');
});
