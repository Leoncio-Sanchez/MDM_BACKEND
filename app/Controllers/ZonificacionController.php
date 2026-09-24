<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Zonificacion;
use App\Requests\Zonificacion\StoreZonificacionRequest;
use App\Requests\Zonificacion\UpdateZonificacionRequest;
use App\Resources\ZonificacionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZonificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $estaActivo = $request->query('esta_activo');
        $query = Zonificacion::query();

        if ($estaActivo === 'true') {
            $query->activos();
        } elseif ($estaActivo === 'false') {
            $query->inactivos();
        }

        $zonificaciones = $query->orderBy('id')->get();

        return response()->json(ZonificacionResource::collection($zonificaciones)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $zonificacion = Zonificacion::findOrFail((int) $id);

        return response()->json((new ZonificacionResource($zonificacion))->resolve());
    }

    public function store(StoreZonificacionRequest $request): JsonResponse
    {
        $user = $request->user('api');
        $data = $request->validated();

        if (! array_key_exists('esta_activo', $data) || $data['esta_activo'] === null) {
            $data['esta_activo'] = true;
        }

        $data['usuario_id'] = $user !== null ? $user->id : 1;
        $data['fecha_digitacion'] = now();

        $zonificacion = Zonificacion::create($data);

        return response()->json((new ZonificacionResource($zonificacion))->resolve(), 201);
    }

    public function update(UpdateZonificacionRequest $request, int|string $id): JsonResponse
    {
        $zonificacion = Zonificacion::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        $user = $request->user('api');
        if ($user) {
            $data['usuario_id'] = $user->id;
        }

        $zonificacion->update($data);

        return response()->json((new ZonificacionResource($zonificacion))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $zonificacion = Zonificacion::findOrFail((int) $id);
        $zonificacion->delete();

        return response()->json(null, 204);
    }
}
