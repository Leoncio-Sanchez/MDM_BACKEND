<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Presentation\Controllers;

use App\Modules\ConsultaPublica\Application\DTOs\ConsultaPublicaDTO;
use App\Modules\ConsultaPublica\Application\UseCases\ConsultarTramitePublicoUseCase;
use App\Modules\ConsultaPublica\Presentation\Requests\ConsultaPublicaRequest;
use App\Modules\ConsultaPublica\Presentation\Resources\TramitePublicoResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class ConsultaPublicaController
{
    public function __construct(private ConsultarTramitePublicoUseCase $consultar) {}

    public function consultar(ConsultaPublicaRequest $request): AnonymousResourceCollection
    {
        $dto = ConsultaPublicaDTO::fromArray($request->validated());

        return TramitePublicoResource::collection($this->consultar->execute($dto));
    }
}
