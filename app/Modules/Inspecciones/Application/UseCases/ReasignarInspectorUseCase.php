<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;

/** Cambia de inspector: la asignación anterior queda cerrada con el motivo, no se borra. */
final readonly class ReasignarInspectorUseCase
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    /** @return array<string, mixed> la nueva asignación */
    public function execute(int $asignacionId, int $nuevoInspectorId, string $motivo, ?string $fechaProgramada, ?string $indicaciones, int $usuarioId): array
    {
        $actual = $this->vigente($asignacionId);
        $expedienteId = (int) $actual['expediente_id'];

        if (! $this->asignaciones->expedienteEsperaInspeccion($expedienteId)) {
            throw AsignacionInvalida::expedienteSinInspeccionPendiente((int) $actual['numero_expediente']);
        }
        if ((int) $actual['inspector_id'] === $nuevoInspectorId) {
            throw AsignacionInvalida::mismoInspector();
        }
        if (! $this->asignaciones->esInspectorActivo($nuevoInspectorId)) {
            throw AsignacionInvalida::inspectorNoValido();
        }

        $indicaciones = $indicaciones === null ? null : (trim($indicaciones) === '' ? null : trim($indicaciones));
        $id = $this->asignaciones->reasignar($asignacionId, $nuevoInspectorId, trim($motivo), $fechaProgramada, $indicaciones, $usuarioId);

        return (array) $this->asignaciones->buscar($id);
    }

    /** @return array<string, mixed> */
    private function vigente(int $asignacionId): array
    {
        $asignacion = $this->asignaciones->buscar($asignacionId);
        if ($asignacion === null || ! $asignacion['vigente']) {
            throw AsignacionNoEncontrada::id($asignacionId);
        }

        return $asignacion;
    }
}
