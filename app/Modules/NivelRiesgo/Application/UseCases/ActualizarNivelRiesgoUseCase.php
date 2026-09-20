<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;
use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Domain\Exceptions\NivelRiesgoNoEncontrado;

final readonly class ActualizarNivelRiesgoUseCase
{
    public function __construct(private NivelRiesgoRepositoryInterface $repository) {}

    public function execute(int $id, GuardarNivelRiesgoDTO $dto): NivelRiesgo
    {
        $entity = $this->repository->buscarPorId($id);
        if (! $entity) {
            throw new NivelRiesgoNoEncontrado();
        }

        $updated = $entity->actualizar($dto->codigo, $dto->nombre, $dto->esta_activo);
        return $this->repository->actualizar($updated);
    }
}
