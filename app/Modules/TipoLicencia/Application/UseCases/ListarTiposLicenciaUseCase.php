<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Application\UseCases;

use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;

final readonly class ListarTiposLicenciaUseCase
{
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}

    /** @return array<TipoLicencia> */
    public function execute(?bool $estaActivo = null): array
    {
        return $this->repo->listar($estaActivo);
    }
}
