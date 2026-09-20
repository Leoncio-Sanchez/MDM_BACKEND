<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Application\UseCases;

use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;
use App\Modules\TipoLicencia\Domain\Exceptions\TipoLicenciaNoEncontrado;

final readonly class ObtenerTipoLicenciaUseCase
{
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}

    public function execute(int $id): TipoLicencia
    {
        $tipo = $this->repo->obtenerPorId($id);
        if (! $tipo) {
            throw new TipoLicenciaNoEncontrado($id);
        }

        return $tipo;
    }
}
