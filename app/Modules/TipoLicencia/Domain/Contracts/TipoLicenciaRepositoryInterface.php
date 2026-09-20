<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Domain\Contracts;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;

interface TipoLicenciaRepositoryInterface {
    /** @return array<TipoLicencia> */
    public function listar(?bool $estaActivo): array;
    public function obtenerPorId(int $id): ?TipoLicencia;
    public function guardar(TipoLicencia $tipo): TipoLicencia;
    public function eliminar(int $id): void;
}
