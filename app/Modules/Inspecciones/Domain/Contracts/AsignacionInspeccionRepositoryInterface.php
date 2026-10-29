<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Contracts;

use App\Modules\Inspecciones\Domain\Exceptions\ExpedienteYaAsignado;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;

/**
 * Asignaciones de inspectores a expedientes que esperan su inspección ITSE.
 *
 * Un expediente "espera inspección" si su procedimiento requiere ITSE, todavía no tiene
 * certificado ni denegatoria de ITSE y no está anulado.
 */
interface AsignacionInspeccionRepositoryInterface
{
    /**
     * Expedientes que esperan inspección, con su asignación vigente (si la tienen) y un
     * local sugerido (de la licencia del mismo expediente o, si no hay, del solicitante).
     *
     * @return array<int, array<string, mixed>>
     */
    public function expedientesPorInspeccionar(): array;

    /**
     * Inspectores activos, con cuántas inspecciones pendientes tiene cada uno.
     *
     * @return array<int, array<string, mixed>>
     */
    public function inspectoresDisponibles(): array;

    public function esInspectorActivo(int $usuarioId): bool;

    public function expedienteEsperaInspeccion(int $expedienteId): bool;

    /**
     * Como expedienteEsperaInspeccion, pero también si ya tiene dictamen final: la oficina
     * aún no emite el certificado ni la denegatoria.
     */
    public function expedientePendienteDeResolucion(int $expedienteId): bool;

    public function numeroExpediente(int $expedienteId): ?int;

    /** @return array<string, mixed>|null */
    public function buscar(int $asignacionId): ?array;

    public function tieneAsignacionVigente(int $expedienteId): bool;

    /** @throws ExpedienteYaAsignado */
    public function crear(int $expedienteId, DatosAsignacion $datos, int $usuarioId): int;

    /**
     * Cierra la asignación vigente y abre otra para el nuevo responsable y sus acompañantes, con el mismo local.
     *
     * @param  array<int, int>  $acompanantes
     */
    public function reasignar(int $asignacionId, int $nuevoInspectorId, array $acompanantes, string $motivo, ?string $fechaProgramada, ?string $indicaciones, int $usuarioId): int;

    /** Cambia local, programación y acompañantes; el responsable no cambia (eso es reasignar). */
    public function actualizar(int $asignacionId, DatosAsignacion $datos, int $usuarioId): void;

    /** @return list<int> ids de los acompañantes vigentes */
    public function acompanantesVigentes(int $asignacionId): array;

    /**
     * Todas las asignaciones del expediente, de la más reciente a la más antigua.
     *
     * @return array<int, array<string, mixed>>
     */
    public function historial(int $expedienteId): array;

    /**
     * Inspecciones pendientes del inspector: las que tiene a cargo y las que acompaña ("es_responsable").
     *
     * @return array<int, array<string, mixed>>
     */
    public function bandeja(int $inspectorId): array;
}
