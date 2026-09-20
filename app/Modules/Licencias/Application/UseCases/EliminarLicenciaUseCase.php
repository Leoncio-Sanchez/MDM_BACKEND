<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
final readonly class EliminarLicenciaUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}
    public function execute(int $id): void {
        $this->licencias->eliminar($id);
    }
}