<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Application\UseCases;

use App\Modules\TipoLicencia\Application\DTOs\GuardarTipoLicenciaDTO;
use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;
use App\Modules\TipoLicencia\Domain\Exceptions\TipoLicenciaNoEncontrado;

final readonly class ActualizarTipoLicenciaUseCase
{
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}

    public function execute(int $id, GuardarTipoLicenciaDTO $dto): TipoLicencia
    {
        if (! $this->repo->obtenerPorId($id)) {
            throw new TipoLicenciaNoEncontrado($id);
        }
        $tipo = new TipoLicencia($id, $dto->codigo, $dto->nombre, $dto->estaActivo);

        return $this->repo->guardar($tipo);
    }
}
