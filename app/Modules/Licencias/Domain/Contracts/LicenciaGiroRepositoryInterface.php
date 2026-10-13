<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Contracts;

interface LicenciaGiroRepositoryInterface
{
    /** @return int[] */
    public function obtenerGiros(int $licenciaId): array;
    public function eliminarGiros(int $licenciaId): void;
    /** @param int[] $giroIds */
    public function insertarGiros(int $licenciaId, array $giroIds, int $usuarioId): void;
}
