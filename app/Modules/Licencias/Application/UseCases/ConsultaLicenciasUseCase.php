<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\ConsultaLicenciasDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
final readonly class ConsultaLicenciasUseCase {
    public function __construct(private LicenciaRepositoryInterface $licencias) {}
    /** @return array<mixed> */
    public function execute(ConsultaLicenciasDTO $dto): array {
        if ($dto->numeroLicencia) {
            return $this->licencias->buscarPorFiltro('NUMERO_LICENCIA', $dto->numeroLicencia);
        }
        return $this->licencias->paginar(1, 100)['items'];
    }
}
