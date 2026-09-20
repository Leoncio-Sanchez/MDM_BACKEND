<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\UseCases;

use App\Modules\Licencias\Application\DTOs\InactivarLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Exceptions\EstadoInactivacionDuplicadoError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarInactivacionUseCase
{
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}

    public function execute(InactivarLicenciaDTO $dto): void
    {
        DB::transaction(function () use ($dto): void {
            $licencia = $this->licencias->buscarPorId($dto->licenciaId);
            if (!$licencia) {
                throw LicenciaNoEncontrada::porId($dto->licenciaId);
            }

            if ($this->licencias->existeEstado($dto->licenciaId, $dto->estadoId)) {
                throw EstadoInactivacionDuplicadoError::porEstado(
                    $dto->licenciaId,
                    $dto->estadoId,
                );
            }

            $this->licencias->insertarEstado(
                licenciaId: $dto->licenciaId,
                estadoId: $dto->estadoId,
                fecha: $dto->fechaEstado->toDateString(),
                documento: $dto->documento,
                observaciones: $dto->observaciones,
            );
        });
    }
}
