<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;

/** Corrige el local o reprograma la visita sin cambiar de inspector. */
final readonly class ActualizarAsignacionUseCase
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    /** @return array<string, mixed> */
    public function execute(int $asignacionId, string $nombreComercial, string $direccion, ?string $fechaProgramada, ?string $indicaciones): array
    {
        $actual = $this->asignaciones->buscar($asignacionId);
        if ($actual === null || ! $actual['vigente']) {
            throw AsignacionNoEncontrada::id($asignacionId);
        }
        if (! $this->asignaciones->expedienteEsperaInspeccion((int) $actual['expediente_id'])) {
            throw AsignacionInvalida::expedienteSinInspeccionPendiente((int) $actual['numero_expediente']);
        }

        $this->asignaciones->actualizar($asignacionId, new DatosAsignacion(
            inspectorId: (int) $actual['inspector_id'],
            nombreComercial: $nombreComercial,
            direccion: $direccion,
            fechaProgramada: $fechaProgramada,
            indicaciones: $indicaciones,
        ));

        return (array) $this->asignaciones->buscar($asignacionId);
    }
}
