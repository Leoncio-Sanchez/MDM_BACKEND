<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Controllers;

use App\Shared\Presentation\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenciaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function buscar(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function reporte(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function consulta(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function verificarExpediente(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function inactivar(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function update(Request $request, $pk): JsonResponse
    {
        return response()->json([]);
    }
}
