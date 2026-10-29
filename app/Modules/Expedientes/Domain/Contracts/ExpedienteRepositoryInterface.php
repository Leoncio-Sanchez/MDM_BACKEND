<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\AnulacionExpediente;

interface ExpedienteRepositoryInterface
{
    /**
     * @return array<int, Expediente>
     */
    public function listar(): array;

    /**
     * @return array{items: array<int, Expediente>, total: int}
     */
    public function paginar(int $pagina = 1, int $porPagina = 15): array;

    public function buscarPorId(int $id): ?Expediente;

    public function crear(Expediente $expediente): Expediente;

    public function actualizar(Expediente $expediente): Expediente;

    /** Registra la anulación (los expedientes no se eliminan: LPAG, Ley 27444) */
    public function anular(int $id, AnulacionExpediente $anulacion): void;

    public function existeNumeroEnAnio(int $numero, int $anio, ?int $exceptoId = null): bool;

    public function siguienteNumeroEnAnio(int $anio): int;

    public function tieneLicencia(int $expedienteId): bool;

    public function tieneItse(int $expedienteId): bool;

    /**
     * Búsqueda por filtro con joins y cálculo de autorizaciones pendientes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array;

    /**
     * Lista expedientes con autorizaciones pendientes (SQL crudo #1).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientes(): array;

    /**
     * @return array<int, Expediente>
     */
    public function consultar(?int $numeroExpediente): array;
}
