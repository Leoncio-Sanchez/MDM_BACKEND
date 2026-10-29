<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\ExpedienteYaAsignado;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;

final readonly class AsignarInspectorUseCase
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    /** @return array<string, mixed> la asignación creada */
    public function execute(int $expedienteId, DatosAsignacion $datos, int $usuarioId): array
    {
        $numero = $this->asignaciones->numeroExpediente($expedienteId)
            ?? throw ExpedienteNoEncontrado::porId($expedienteId);

        if (! $this->asignaciones->expedienteEsperaInspeccion($expedienteId)) {
            throw AsignacionInvalida::expedienteSinInspeccionPendiente($numero);
        }
        if ($this->asignaciones->tieneAsignacionVigente($expedienteId)) {
            throw ExpedienteYaAsignado::numero($numero);
        }
        if (! $this->asignaciones->esInspectorActivo($datos->inspectorId)) {
            throw AsignacionInvalida::inspectorNoValido();
        }
        foreach ($datos->acompanantes as $acompanante) {
            if (! $this->asignaciones->esInspectorActivo($acompanante)) {
                throw AsignacionInvalida::acompananteNoValido();
            }
        }

        $id = $this->asignaciones->crear($expedienteId, $datos, $usuarioId);

        return (array) $this->asignaciones->buscar($id);
    }
}
