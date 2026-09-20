<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Contracts;

use App\Modules\Licencias\Domain\Entities\Licencia;

interface LicenciaRepositoryInterface
{
    public function buscar(string $filtro, string $valor): array;
    public function obtenerPorId(int $id): ?Licencia;
    public function crear(Licencia $licencia): Licencia;
    public function modificar(Licencia $licencia): Licencia;
    public function eliminar(int $id): void;
}
