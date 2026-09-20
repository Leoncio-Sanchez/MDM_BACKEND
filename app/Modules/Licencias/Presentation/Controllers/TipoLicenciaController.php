<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Modules\Licencias\Infrastructure\Persistence\Models\TipoLicenciaReadModel;
use App\Modules\Licencias\Presentation\Resources\TipoLicenciaResource;

final class TipoLicenciaController extends Controller
{
    public function index(): JsonResponse
    {
        $tipos = TipoLicenciaReadModel::all();
        // Since we removed data wrapper, we return array directly
        return response()->json(TipoLicenciaResource::collection($tipos));
    }
}
