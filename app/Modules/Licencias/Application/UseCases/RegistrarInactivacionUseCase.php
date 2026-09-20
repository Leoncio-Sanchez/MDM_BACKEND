<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\InactivarLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
final readonly class RegistrarInactivacionUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}
    /** @return array<mixed> */
    public function execute(InactivarLicenciaDTO $dto): array {
        $_ = $this->licencias;
        return [];
    }
}