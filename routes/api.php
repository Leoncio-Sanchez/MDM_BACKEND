<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Endpoint fecha-servidor compatible con Django
Route::middleware('auth:api')->prefix('lf-itse')->group(function (): void {
    Route::get('fecha-servidor', function () {
        return response()->json([
            'fecha' => now()->setTimezone('America/Lima')->format('Y-m-d'),
        ]);
    });
});
