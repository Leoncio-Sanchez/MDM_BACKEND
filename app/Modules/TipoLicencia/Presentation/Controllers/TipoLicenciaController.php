<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Presentation\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\TipoLicencia\Application\UseCases\ListarTiposLicenciaUseCase;
use App\Modules\TipoLicencia\Application\UseCases\ObtenerTipoLicenciaUseCase;
use App\Modules\TipoLicencia\Application\UseCases\CrearTipoLicenciaUseCase;
use App\Modules\TipoLicencia\Application\UseCases\ActualizarTipoLicenciaUseCase;
use App\Modules\TipoLicencia\Application\UseCases\EliminarTipoLicenciaUseCase;
use App\Modules\TipoLicencia\Application\DTOs\GuardarTipoLicenciaDTO;
use App\Modules\TipoLicencia\Presentation\Requests\GuardarTipoLicenciaRequest;
use App\Modules\TipoLicencia\Presentation\Resources\TipoLicenciaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TipoLicenciaController extends Controller {
    public function index(Request $request, ListarTiposLicenciaUseCase $useCase): AnonymousResourceCollection {
        $estaActivo = $request->query('esta_activo');
        $filtro = null;
        if ($estaActivo === 'true') $filtro = true;
        elseif ($estaActivo === 'false') $filtro = false;
        
        return TipoLicenciaResource::collection($useCase->execute($filtro));
    }
    public function show(int $id, ObtenerTipoLicenciaUseCase $useCase): TipoLicenciaResource {
        return new TipoLicenciaResource($useCase->execute($id));
    }
    public function store(GuardarTipoLicenciaRequest $request, CrearTipoLicenciaUseCase $useCase): JsonResponse {
        $tipo = $useCase->execute(GuardarTipoLicenciaDTO::fromArray($request->validated()));
        return (new TipoLicenciaResource($tipo))->response()->setStatusCode(201);
    }
    public function update(int $id, GuardarTipoLicenciaRequest $request, ActualizarTipoLicenciaUseCase $useCase): TipoLicenciaResource {
        return new TipoLicenciaResource($useCase->execute($id, GuardarTipoLicenciaDTO::fromArray($request->validated())));
    }
    public function destroy(int $id, EliminarTipoLicenciaUseCase $useCase): JsonResponse {
        $useCase->execute($id);
        return response()->json(null, 204);
    }
}
