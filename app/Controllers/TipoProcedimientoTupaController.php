<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TipoProcedimientoTupa;
use App\Requests\TipoProcedimientoTupa\StoreTipoProcedimientoTupaRequest;
use App\Requests\TipoProcedimientoTupa\UpdateTipoProcedimientoTupaRequest;
use App\Resources\TipoProcedimientoTupaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoProcedimientoTupaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estaActivo = $request->query('esta_activo');
        $query = TipoProcedimientoTupa::query();

        if ($estaActivo === 'true') {
            $query->activos();
        } elseif ($estaActivo === 'false') {
            $query->inactivos();
        }

        $tipos = $query->orderBy('nombre')->get();

        return response()->json(TipoProcedimientoTupaResource::collection($tipos)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $tipo = TipoProcedimientoTupa::findOrFail((int) $id);

        return response()->json((new TipoProcedimientoTupaResource($tipo))->resolve());
    }

    public function store(StoreTipoProcedimientoTupaRequest $request): JsonResponse
    {
        $user = $request->user('api');
        $data = $request->validated();

        if (isset($data['codigo'])) {
            $data['codigo'] = strtoupper(trim((string) $data['codigo']));
        }
        if (isset($data['nombre'])) {
            $data['nombre'] = trim((string) $data['nombre']);
        }
        if (! array_key_exists('esta_activo', $data) || $data['esta_activo'] === null) {
            $data['esta_activo'] = true;
        }
        if (! array_key_exists('requiere_lf', $data) || $data['requiere_lf'] === null) {
            $data['requiere_lf'] = false;
        }
        if (! array_key_exists('requiere_itse', $data) || $data['requiere_itse'] === null) {
            $data['requiere_itse'] = false;
        }
        if (isset($data['unidad_organica']) && ! isset($data['unidad_organica_id'])) {
            $data['unidad_organica_id'] = $data['unidad_organica'];
        }
        unset($data['unidad_organica']);

        $data['usuario_id'] = $user !== null ? $user->id : 1;
        $data['fecha_digitacion'] = now();

        $tipo = TipoProcedimientoTupa::create($data);

        return response()->json((new TipoProcedimientoTupaResource($tipo))->resolve(), 201);
    }

    public function update(UpdateTipoProcedimientoTupaRequest $request, int|string $id): JsonResponse
    {
        $tipo = TipoProcedimientoTupa::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        if (isset($data['codigo'])) {
            $data['codigo'] = strtoupper(trim((string) $data['codigo']));
        }
        if (isset($data['nombre'])) {
            $data['nombre'] = trim((string) $data['nombre']);
        }
        if (isset($data['unidad_organica']) && ! isset($data['unidad_organica_id'])) {
            $data['unidad_organica_id'] = $data['unidad_organica'];
        }
        unset($data['unidad_organica']);

        $user = $request->user('api');
        if ($user) {
            $data['usuario_id'] = $user->id;
        }

        $tipo->update($data);

        return response()->json((new TipoProcedimientoTupaResource($tipo))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $tipo = TipoProcedimientoTupa::findOrFail((int) $id);
        $tipo->delete();

        return response()->json(null, 204);
    }
}
