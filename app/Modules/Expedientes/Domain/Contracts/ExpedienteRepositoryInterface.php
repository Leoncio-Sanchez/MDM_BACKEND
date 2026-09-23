<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

use App\Modules\Expedientes\Domain\Entities\Expediente;

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

    public function eliminar(int $id): void;

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
     * Consulta rápida de expedientes con filtro opcional por numero_expediente.
     *
     * @return array<int, Expediente>
     */
    public function consultaRapida(?int $numeroExpediente): array;
}
