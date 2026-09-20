<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Application\UseCases;
use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;

final readonly class ListarTiposLicenciaUseCase {
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}
    /** @return array<\App\Modules\TipoLicencia\Domain\Entities\TipoLicencia> */
    public function execute(?bool $estaActivo = null): array {
        return $this->repo->listar($estaActivo);
    }
}
