<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;

/** Corrige el local, reprograma la visita o cambia los acompañantes sin cambiar de responsable. */
final readonly class ActualizarAsignacionUseCase
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    /**
     * @param  array<int, int>|null  $acompanantes  null deja el equipo como está
     * @return array<string, mixed>
     */
    public function execute(int $asignacionId, string $nombreComercial, string $direccion, ?string $fechaProgramada, ?string $indicaciones, ?array $acompanantes, int $usuarioId): array
    {
        $actual = $this->asignaciones->buscar($asignacionId);
        if ($actual === null || ! $actual['vigente']) {
            throw AsignacionNoEncontrada::id($asignacionId);
        }
        if (! $this->asignaciones->expedienteEsperaInspeccion((int) $actual['expediente_id'])) {
            throw AsignacionInvalida::expedienteSinInspeccionPendiente((int) $actual['numero_expediente']);
        }

        $previos = $this->asignaciones->acompanantesVigentes($asignacionId);
        $datos = new DatosAsignacion(
            inspectorId: (int) $actual['inspector_id'],
            nombreComercial: $nombreComercial,
            direccion: $direccion,
            fechaProgramada: $fechaProgramada,
            indicaciones: $indicaciones,
            acompanantes: $acompanantes ?? $previos,
        );
        // Solo se validan los que se suman: quien ya acompañaba puede seguir aunque luego lo desactiven
        foreach (array_diff($datos->acompanantes, $previos) as $nuevo) {
            if (! $this->asignaciones->esInspectorActivo($nuevo)) {
                throw AsignacionInvalida::acompananteNoValido();
            }
        }

        $this->asignaciones->actualizar($asignacionId, $datos, $usuarioId);

        return (array) $this->asignaciones->buscar($asignacionId);
    }
}
