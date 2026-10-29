<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

interface ExpedienteReadRepositoryInterface
{
    public function existe(int $id): bool;

    public function estaAnulado(int $id): bool;

    /**
     * @param int $numero
     * @param int $anio
     * @return \stdClass|null
     */
    public function buscarPorNumeroYAnio(int $numero, int $anio): ?object;

    public function buscarPorId(int $id): ?object;
}
