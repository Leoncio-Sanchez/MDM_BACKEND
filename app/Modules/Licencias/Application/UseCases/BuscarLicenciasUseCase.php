<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\BuscarLicenciasDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
final readonly class BuscarLicenciasUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}
    /** @return array<mixed> */
    public function execute(BuscarLicenciasDTO $dto): array {
        $_ = $this->licencias;
        return [];
    }
}