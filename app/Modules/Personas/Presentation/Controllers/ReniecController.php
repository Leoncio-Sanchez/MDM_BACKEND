<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Personas\Application\DTOs\ConsultarReniecDTO;
use App\Modules\Personas\Application\UseCases\ConsultarReniecUseCase;
use App\Modules\Personas\Presentation\Requests\ConsultarReniecRequest;
use App\Modules\Personas\Presentation\Resources\PersonaReniecResource;

final class ReniecController extends Controller
{
    public function __construct(
        private readonly ConsultarReniecUseCase $consultarReniec,
    ) {}

    public function consultar(ConsultarReniecRequest $request): PersonaReniecResource
    {
        $dto = $this->consultarReniec->execute(new ConsultarReniecDTO($request->validated('dni')));

        return new PersonaReniecResource($dto);
    }
}
