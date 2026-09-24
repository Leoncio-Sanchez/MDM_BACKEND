<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UnidadOrganica;
use App\Resources\UnidadOrganicaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnidadOrganicaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estaActivo = $request->query('esta_activo');
        $query = UnidadOrganica::query();

        if ($estaActivo === 'true') {
            $query->activos();
        } elseif ($estaActivo === 'false') {
            $query->inactivos();
        }

        $unidades = $query->orderBy('nombre')->get();

        return response()->json(UnidadOrganicaResource::collection($unidades)->resolve());
    }
}
