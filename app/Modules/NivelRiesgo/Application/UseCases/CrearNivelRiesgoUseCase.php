<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;
use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;

final readonly class CrearNivelRiesgoUseCase
{
    public function __construct(private NivelRiesgoRepositoryInterface $repository) {}

    public function execute(GuardarNivelRiesgoDTO $dto): NivelRiesgo
    {
        $entity = NivelRiesgo::crear($dto->codigo, $dto->nombre, $dto->esta_activo);
        return $this->repository->guardar($entity);
    }
}
