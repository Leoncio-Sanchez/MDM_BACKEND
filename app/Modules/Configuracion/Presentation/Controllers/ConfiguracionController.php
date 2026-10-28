<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Presentation\Controllers;

use App\Modules\Configuracion\Domain\Contracts\ConfiguracionRepositoryInterface;
use App\Modules\Configuracion\Domain\ValueObjects\ConfiguracionInforme;
use App\Modules\Configuracion\Presentation\Requests\ActualizarConfiguracionInformeRequest;
use Illuminate\Http\JsonResponse;

final readonly class ConfiguracionController
{
    public function __construct(private ConfiguracionRepositoryInterface $configuracion) {}

    public function informe(): JsonResponse
    {
        return new JsonResponse($this->aRespuesta($this->configuracion->informe()));
    }

    public function actualizarInforme(ActualizarConfiguracionInformeRequest $request): JsonResponse
    {
        $nueva = new ConfiguracionInforme(
            entidad: trim((string) $request->validated('entidad')),
            nombreAnio: trim((string) $request->validated('nombre_anio')),
            gerencia: trim((string) $request->validated('gerencia')),
            siglas: trim((string) $request->validated('siglas')),
            firmanteNombre: trim((string) $request->validated('firmante_nombre')),
            firmanteCargo: trim((string) $request->validated('firmante_cargo')),
        );
        $this->configuracion->guardarInforme($nueva, (int) $request->user()?->getAuthIdentifier());

        return new JsonResponse($this->aRespuesta($nueva));
    }

    /** @return array<string, string> */
    private function aRespuesta(ConfiguracionInforme $c): array
    {
        return [
            'entidad' => $c->entidad,
            'nombre_anio' => $c->nombreAnio,
            'gerencia' => $c->gerencia,
            'siglas' => $c->siglas,
            'firmante_nombre' => $c->firmanteNombre,
            'firmante_cargo' => $c->firmanteCargo,
        ];
    }
}
