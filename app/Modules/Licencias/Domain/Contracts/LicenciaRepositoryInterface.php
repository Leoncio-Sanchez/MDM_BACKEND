<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\Contracts;

use App\Modules\Licencias\Domain\Entities\Licencia;

interface LicenciaRepositoryInterface
{
    /** @return array{items: Licencia[], total: int} */
    public function paginar(int $pagina, int $porPagina): array;

    public function buscarPorId(int $id): ?Licencia;

    /** @return Licencia[] */
    public function buscarPorFiltro(string $filtro, string $valor): array;

    public function crear(Licencia $licencia): Licencia;

    public function actualizar(Licencia $licencia): Licencia;

    public function eliminar(int $id): void;

    public function inactivar(int $id): Licencia;
}
