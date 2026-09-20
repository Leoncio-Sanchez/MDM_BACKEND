<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Application\UseCases;
use App\Modules\TipoLicencia\Application\DTOs\GuardarTipoLicenciaDTO;
use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;

final readonly class CrearTipoLicenciaUseCase {
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}
    public function execute(GuardarTipoLicenciaDTO $dto): TipoLicencia {
        $tipo = new TipoLicencia(null, $dto->codigo, $dto->nombre, $dto->estaActivo);
        return $this->repo->guardar($tipo);
    }
}
