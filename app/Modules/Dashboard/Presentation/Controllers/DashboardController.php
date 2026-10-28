<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Presentation\Controllers;

use App\Modules\Dashboard\Application\UseCases\ObtenerResumenGeneralUseCase;
use App\Modules\Dashboard\Presentation\Resources\ResumenGeneralResource;

final readonly class DashboardController
{
    public function __construct(private ObtenerResumenGeneralUseCase $obtenerResumen) {}

    public function resumen(): ResumenGeneralResource
    {
        return new ResumenGeneralResource($this->obtenerResumen->execute());
    }
}
