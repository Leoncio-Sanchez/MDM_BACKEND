<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NivelRiesgo;
use App\Requests\NivelRiesgo\StoreNivelRiesgoRequest;
use App\Requests\NivelRiesgo\UpdateNivelRiesgoRequest;
use App\Resources\NivelRiesgoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NivelRiesgoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estaActivo = $request->query('esta_activo');
        $query = NivelRiesgo::query();

        if ($estaActivo === 'true') {
            $query->activos();
        } elseif ($estaActivo === 'false') {
            $query->inactivos();
        }

        $niveles = $query->orderBy('id')->get();

        return response()->json(NivelRiesgoResource::collection($niveles)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $nivel = NivelRiesgo::findOrFail((int) $id);

        return response()->json((new NivelRiesgoResource($nivel))->resolve());
    }

    public function store(StoreNivelRiesgoRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('esta_activo', $data) || $data['esta_activo'] === null) {
            $data['esta_activo'] = true;
        }

        $nivel = NivelRiesgo::create($data);

        return response()->json((new NivelRiesgoResource($nivel))->resolve(), 201);
    }

    public function update(UpdateNivelRiesgoRequest $request, int|string $id): JsonResponse
    {
        $nivel = NivelRiesgo::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        $nivel->update($data);

        return response()->json((new NivelRiesgoResource($nivel))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $nivel = NivelRiesgo::findOrFail((int) $id);
        $nivel->delete();

        return response()->json(null, 204);
    }
}
