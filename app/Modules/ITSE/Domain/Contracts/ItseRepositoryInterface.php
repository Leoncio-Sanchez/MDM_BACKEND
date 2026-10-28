<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

use App\Modules\ITSE\Domain\Entities\Itse;

interface ItseRepositoryInterface
{
    public function crear(Itse $itse): Itse;
    public function actualizar(Itse $itse): Itse;
    public function buscarPorId(int $id): ?Itse;
    public function existePorId(int $id): bool;

    public function existeNumero(int $numero, ?int $excluirId = null): bool;
    public function existeRecibo(string $recibo, ?int $excluirId = null): bool;

    /**
     * @param string $filtro
     * @param string $valor
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array;

    /**
     * ITSE que caducan entre las fechas (Y-m-d), sin inactivar y aún no renovados.
     *
     * @return array<int, Itse>
     */
    public function listarPorRenovar(string $fechaDesde, string $fechaHasta): array;

    /**
     * Consulta rápida con filtro opcional por numero_itse (6B).
     *
     * @return array<int, Itse>
     */
    public function consultaRapida(?int $numeroItse): array;
}

