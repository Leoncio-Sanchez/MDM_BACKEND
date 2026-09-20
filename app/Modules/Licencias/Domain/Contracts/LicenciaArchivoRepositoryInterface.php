<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Contracts;

interface LicenciaArchivoRepositoryInterface
{
    /** @return string[] */
    public function obtenerRutas(int $licenciaId): array;
    public function eliminarRegistros(int $licenciaId): void;
}
