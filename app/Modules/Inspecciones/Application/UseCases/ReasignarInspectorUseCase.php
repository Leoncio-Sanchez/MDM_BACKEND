<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;

/** Cambia de inspector responsable: la asignación anterior queda cerrada con el motivo, no se borra. */
final readonly class ReasignarInspectorUseCase
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    /**
     * @param  array<int, int>|null  $acompanantes  null conserva los acompañantes actuales (sin el nuevo responsable)
     * @return array<string, mixed> la nueva asignación
     */
    public function execute(int $asignacionId, int $nuevoInspectorId, ?array $acompanantes, string $motivo, ?string $fechaProgramada, ?string $indicaciones, int $usuarioId): array
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

        $previos = $this->asignaciones->acompanantesVigentes($asignacionId);
        // Si un acompañante pasa a responsable, deja de figurar como acompañante
        $acompanantes ??= array_values(array_diff($previos, [$nuevoInspectorId]));
        $datos = new DatosAsignacion(
            inspectorId: $nuevoInspectorId,
            nombreComercial: (string) $actual['nombre_comercial'],
            direccion: (string) $actual['direccion'],
            fechaProgramada: $fechaProgramada,
            indicaciones: $indicaciones,
            acompanantes: $acompanantes,
        );
        foreach (array_diff($datos->acompanantes, $previos) as $nuevo) {
            if (! $this->asignaciones->esInspectorActivo($nuevo)) {
                throw AsignacionInvalida::acompananteNoValido();
            }
        }

        $id = $this->asignaciones->reasignar($asignacionId, $nuevoInspectorId, $datos->acompanantes, trim($motivo), $datos->fechaProgramada, $datos->indicaciones, $usuarioId);

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
