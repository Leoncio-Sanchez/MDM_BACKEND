<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inspectores\Application\UseCases\ActualizarInspectorUseCase;
use App\Modules\Inspectores\Application\UseCases\CrearInspectorUseCase;
use App\Modules\Inspectores\Application\UseCases\EliminarInspectorUseCase;
use App\Modules\Inspectores\Application\UseCases\ListarInspectoresUseCase;
use App\Modules\Inspectores\Application\UseCases\ObtenerInspectorUseCase;
use App\Resources\UsuarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InspectorController extends Controller
{
    public function __construct(
        private readonly ListarInspectoresUseCase $listarInspectores,
        private readonly ObtenerInspectorUseCase $obtenerInspector,
        private readonly CrearInspectorUseCase $crearInspector,
        private readonly ActualizarInspectorUseCase $actualizarInspector,
        private readonly EliminarInspectorUseCase $eliminarInspector,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $busqueda = $request->query('busqueda');
        $inspectores = $this->listarInspectores->execute(is_string($busqueda) ? $busqueda : null);

        return response()->json(
            UsuarioResource::collection($inspectores)->resolve()
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->only([
            'username',
            'password',
            'first_name',
            'last_name',
            'email',
            'is_active',
        ]);

        if (empty($datos['username'])) {
            return response()->json(['error' => 'El username es requerido.'], 400);
        }

        $inspector = $this->crearInspector->execute($datos);

        return response()->json((new UsuarioResource($inspector))->resolve(), 201);
    }

    public function show(int $pk): JsonResponse
    {
        $inspector = $this->obtenerInspector->execute($pk);
        if (! $inspector) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new UsuarioResource($inspector))->resolve(), 200);
    }

    public function update(Request $request, int $pk): JsonResponse
    {
        $datos = $request->only([
            'first_name',
            'last_name',
            'email',
            'is_active',
        ]);

        $inspector = $this->actualizarInspector->execute($pk, $datos);
        if (! $inspector) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new UsuarioResource($inspector))->resolve(), 200);
    }

    public function destroy(int $pk): JsonResponse
    {
        $exito = $this->eliminarInspector->execute($pk);
        if (! $exito) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json(null, 204);
    }
}
