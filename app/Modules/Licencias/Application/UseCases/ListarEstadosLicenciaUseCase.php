<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\UseCases;

use App\Modules\Licencias\Application\DTOs\ListarEstadosDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;

final readonly class ListarEstadosLicenciaUseCase
{
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(ListarEstadosDTO $dto): array
    {
        $licencia = $this->licencias->buscarPorId($dto->licenciaId);
        if (!$licencia) {
            throw LicenciaNoEncontrada::porId($dto->licenciaId);
        }

        return $this->licencias->listarEstados($dto->licenciaId);
    }
}
