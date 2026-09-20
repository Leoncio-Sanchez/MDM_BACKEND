<?php
use Illuminate\Support\Facades\Route;
use App\Modules\TipoLicencia\Presentation\Controllers\TipoLicenciaController;

Route::middleware('api')->prefix('api/lf-itse')->group(function () {
    Route::apiResource('tipos-licencia', TipoLicenciaController::class)->parameters([
        'tipos-licencia' => 'id'
    ]);
});
