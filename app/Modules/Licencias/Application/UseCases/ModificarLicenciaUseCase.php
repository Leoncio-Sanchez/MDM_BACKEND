<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\ModificarLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
final readonly class ModificarLicenciaUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}
    /** @return array<mixed> */
    public function execute(ModificarLicenciaDTO $dto): array {
        $_ = $this->licencias;
        return [];
    }
}