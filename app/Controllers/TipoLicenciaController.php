<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TipoLicencia;
use App\Requests\TipoLicencia\StoreTipoLicenciaRequest;
use App\Requests\TipoLicencia\UpdateTipoLicenciaRequest;
use App\Resources\TipoLicenciaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoLicenciaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estaActivo = $request->query('esta_activo');
        $query = TipoLicencia::query();

        if ($estaActivo === 'true') {
            $query->activos();
        } elseif ($estaActivo === 'false') {
            $query->inactivos();
        }

        $tipos = $query->orderBy('id')->get();

        return response()->json(TipoLicenciaResource::collection($tipos)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $tipo = TipoLicencia::findOrFail((int) $id);

        return response()->json((new TipoLicenciaResource($tipo))->resolve());
    }

    public function store(StoreTipoLicenciaRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('esta_activo', $data) || $data['esta_activo'] === null) {
            $data['esta_activo'] = true;
        }

        $tipo = TipoLicencia::create($data);

        return response()->json((new TipoLicenciaResource($tipo))->resolve(), 201);
    }

    public function update(UpdateTipoLicenciaRequest $request, int|string $id): JsonResponse
    {
        $tipo = TipoLicencia::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        $tipo->update($data);

        return response()->json((new TipoLicenciaResource($tipo))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $tipo = TipoLicencia::findOrFail((int) $id);
        $tipo->delete();

        return response()->json(null, 204);
    }
}
