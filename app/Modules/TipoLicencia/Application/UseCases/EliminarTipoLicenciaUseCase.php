<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Application\UseCases;
use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Exceptions\TipoLicenciaNoEncontrado;

final readonly class EliminarTipoLicenciaUseCase {
    public function __construct(private TipoLicenciaRepositoryInterface $repo) {}
    public function execute(int $id): void {
        if (!$this->repo->obtenerPorId($id)) throw new TipoLicenciaNoEncontrado($id);
        $this->repo->eliminar($id);
    }
}
