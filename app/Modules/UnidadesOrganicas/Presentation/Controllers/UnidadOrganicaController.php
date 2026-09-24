<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UnidadesOrganicas\Application\UseCases\ListarUnidadesOrganicasUseCase;
use App\Modules\UnidadesOrganicas\Presentation\Resources\UnidadOrganicaResource;
use Illuminate\Http\JsonResponse;

final class UnidadOrganicaController extends Controller
{
    public function __construct(
        private readonly ListarUnidadesOrganicasUseCase $listarUseCase
    ) {}

    public function index(): JsonResponse
    {
        $items = $this->listarUseCase->execute();

        return response()->json(
            UnidadOrganicaResource::collection($items)->resolve()
        );
    }
}
