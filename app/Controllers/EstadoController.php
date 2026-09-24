<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Estado;
use App\Requests\Estados\StoreEstadoRequest;
use App\Requests\Estados\UpdateEstadoRequest;
use App\Resources\EstadoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);

        $query = Estado::query()->orderBy('id');
        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'count' => $total,
            'next' => null,
            'previous' => null,
            'results' => EstadoResource::collection($items)->resolve(),
        ]);
    }

    public function store(StoreEstadoRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('esta_activo', $data)) {
            $data['esta_activo'] = false;
        }

        $estado = Estado::create($data);

        return (new EstadoResource($estado))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): JsonResponse
    {
        $estado = Estado::findOrFail($id);

        return (new EstadoResource($estado))->response();
    }

    public function update(UpdateEstadoRequest $request, int $id): JsonResponse
    {
        $estado = Estado::findOrFail($id);
        $estado->update($request->validated());

        return (new EstadoResource($estado))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $estado = Estado::findOrFail($id);
        $estado->delete();

        return response()->json(null, 204);
    }

    public function inactivosItse(): JsonResponse
    {
        $estados = Estado::query()
            ->where('esta_activo', false)
            ->where('es_para_itse', true)
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json(EstadoResource::collection($estados)->resolve());
    }

    public function inactivosLf(): JsonResponse
    {
        $estados = Estado::query()
            ->where('esta_activo', false)
            ->where('es_para_lf', true)
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json(EstadoResource::collection($estados)->resolve());
    }
}
