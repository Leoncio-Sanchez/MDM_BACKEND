<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;

use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;

final class EloquentLicenciaRepository implements LicenciaRepositoryInterface
{
    public function buscar(string $filtro, string $valor): array
    {
        return [];
    }

    public function obtenerPorId(int $id): ?Licencia
    {
        return null;
    }

    public function crear(Licencia $licencia): Licencia
    {
        return $licencia;
    }

    public function modificar(Licencia $licencia): Licencia
    {
        return $licencia;
    }

    public function eliminar(int $id): void {}
}
