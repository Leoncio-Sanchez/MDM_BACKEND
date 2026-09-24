<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TipoDocumentoIdentidad;
use App\Requests\TipoDocumentoIdentidad\ListarTipoDocumentoIdentidadRequest;
use App\Requests\TipoDocumentoIdentidad\StoreTipoDocumentoIdentidadRequest;
use App\Requests\TipoDocumentoIdentidad\UpdateTipoDocumentoIdentidadRequest;
use App\Resources\TipoDocumentoIdentidadResource;
use Illuminate\Http\JsonResponse;

class TipoDocumentoIdentidadController extends Controller
{
    public function index(ListarTipoDocumentoIdentidadRequest $request): JsonResponse
    {
        $tipoPersona = $request->query('tipo_persona');
        if ($tipoPersona === null || $tipoPersona === '') {
            return response()->json(['error' => "El parámetro 'tipo_persona' es obligatorio."], 400);
        }

        $tipoPersona = trim((string) $tipoPersona);
        if (! in_array($tipoPersona, ['N', 'J'], true)) {
            return response()->json(['error' => "tipo_persona '{$tipoPersona}' no válido. Opciones: N (natural), J (jurídica)."], 400);
        }

        $query = TipoDocumentoIdentidad::query();
        if ($tipoPersona === 'N') {
            $query->paraNatural();
        } else {
            $query->paraJuridica();
        }

        $items = $query->orderBy('id')->get();

        return response()->json(TipoDocumentoIdentidadResource::collection($items)->resolve());
    }

    public function show(int|string $id): JsonResponse
    {
        $tipo = TipoDocumentoIdentidad::findOrFail((int) $id);

        return response()->json((new TipoDocumentoIdentidadResource($tipo))->resolve());
    }

    public function store(StoreTipoDocumentoIdentidadRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (! array_key_exists('esta_activo', $data) || $data['esta_activo'] === null) {
            $data['esta_activo'] = true;
        }
        if (! array_key_exists('es_para_natural', $data) || $data['es_para_natural'] === null) {
            $data['es_para_natural'] = true;
        }
        if (! array_key_exists('es_para_juridica', $data) || $data['es_para_juridica'] === null) {
            $data['es_para_juridica'] = false;
        }

        $tipo = TipoDocumentoIdentidad::create($data);

        return response()->json((new TipoDocumentoIdentidadResource($tipo))->resolve(), 201);
    }

    public function update(UpdateTipoDocumentoIdentidadRequest $request, int|string $id): JsonResponse
    {
        $tipo = TipoDocumentoIdentidad::findOrFail((int) $id);
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        $tipo->update($data);

        return response()->json((new TipoDocumentoIdentidadResource($tipo))->resolve());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $tipo = TipoDocumentoIdentidad::findOrFail((int) $id);
        $tipo->delete();

        return response()->json(null, 204);
    }
}
