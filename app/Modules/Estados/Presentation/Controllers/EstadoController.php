<?php
declare(strict_types=1);

namespace App\Modules\Estados\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Estados\Application\DTOs\ListarEstadosDTO;
use App\Modules\Estados\Application\DTOs\CrearEstadoDTO;
use App\Modules\Estados\Application\DTOs\ActualizarEstadoDTO;
use App\Modules\Estados\Application\UseCases\ListarEstadosUseCase;
use App\Modules\Estados\Application\UseCases\ObtenerEstadoUseCase;
use App\Modules\Estados\Application\UseCases\CrearEstadoUseCase;
use App\Modules\Estados\Application\UseCases\ActualizarEstadoUseCase;
use App\Modules\Estados\Application\UseCases\EliminarEstadoUseCase;
use App\Modules\Estados\Presentation\Requests\ListarEstadosRequest;
use App\Modules\Estados\Presentation\Requests\CrearEstadoRequest;
use App\Modules\Estados\Presentation\Requests\ActualizarEstadoRequest;
use App\Modules\Estados\Presentation\Resources\EstadoResource;
use App\Modules\Shared\Presentation\Resources\DjangoPaginatedResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EstadoController extends Controller
{
    #[OA\Get(
        path: "/api/lf-itse/estados/",
        summary: "Listar estados",
        security: [["sanctum" => []]],
        tags: ["Estados"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", required: false)
        ],
        responses: [
            new OA\Response(response: 200, description: "Operación exitosa")
        ]
    )]
    public function index(ListarEstadosRequest $request, ListarEstadosUseCase $useCase): JsonResponse
    {
        $dto = ListarEstadosDTO::fromRequest($request);
        $paginador = $useCase->execute($dto->pagina, $dto->porPagina);
        
        return response()->json(new DjangoPaginatedResource($paginador, EstadoResource::class));
    }

    #[OA\Post(
        path: "/api/lf-itse/estados/",
        summary: "Crear estado",
        security: [["sanctum" => []]],
        tags: ["Estados"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Registrado"),
                    new OA\Property(property: "es_para_lf", type: "boolean", example: true),
                    new OA\Property(property: "es_para_itse", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Creado")
        ]
    )]
    public function store(CrearEstadoRequest $request, CrearEstadoUseCase $useCase): JsonResponse
    {
        $dto = CrearEstadoDTO::fromRequest($request);
        $estado = $useCase->execute($dto);
        
        return response()->json(new EstadoResource($estado), 201);
    }

    #[OA\Get(
        path: "/api/lf-itse/estados/{id}/",
        summary: "Obtener estado",
        security: [["sanctum" => []]],
        tags: ["Estados"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        responses: [
            new OA\Response(response: 200, description: "Operación exitosa")
        ]
    )]
    public function show(int $id, ObtenerEstadoUseCase $useCase): JsonResponse
    {
        $estado = $useCase->execute($id);
        
        return response()->json(new EstadoResource($estado));
    }

    #[OA\Put(
        path: "/api/lf-itse/estados/{id}/",
        summary: "Actualizar estado",
        security: [["sanctum" => []]],
        tags: ["Estados"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Registrado V2")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Actualizado")
        ]
    )]
    public function update(int $id, ActualizarEstadoRequest $request, ActualizarEstadoUseCase $useCase): JsonResponse
    {
        $dto = ActualizarEstadoDTO::fromRequest($request, $id);
        $estado = $useCase->execute($dto);
        
        return response()->json(new EstadoResource($estado));
    }

    #[OA\Delete(
        path: "/api/lf-itse/estados/{id}/",
        summary: "Eliminar estado",
        security: [["sanctum" => []]],
        tags: ["Estados"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true)
        ],
        responses: [
            new OA\Response(response: 204, description: "Sin contenido")
        ]
    )]
    public function destroy(int $id, EliminarEstadoUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);
        
        return response()->json(null, 204);
    }
}
