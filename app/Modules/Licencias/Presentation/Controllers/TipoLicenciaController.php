<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Licencias\Infrastructure\Persistence\Models\TipoLicenciaReadModel;
use App\Modules\Licencias\Presentation\Resources\TipoLicenciaResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TipoLicenciaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TipoLicenciaResource::collection(TipoLicenciaReadModel::orderBy('id')->get());
    }
}
