<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\UseCases;

use App\Modules\Licencias\Application\DTOs\RegistrarNotificacionDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\NotificacionFechaInvalidaError;
use Illuminate\Support\Facades\DB;

final readonly class RegistrarNotificacionUseCase
{
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
    ) {}

    public function execute(RegistrarNotificacionDTO $dto): Licencia
    {
        return DB::transaction(function () use ($dto): Licencia {
            $licencia = $this->licencias->buscarPorId($dto->licenciaId);
            if (!$licencia) {
                throw LicenciaNoEncontrada::porId($dto->licenciaId);
            }

            if ($dto->fechaNotificacion->lt($licencia->fechaEmision)) {
                throw NotificacionFechaInvalidaError::porFecha(
                    $dto->fechaNotificacion->toDateString(),
                    $licencia->fechaEmision->toDateString(),
                );
            }

            $actualizada = $licencia->conFechaNotificacion($dto->fechaNotificacion);

            $this->licencias->actualizarFechaNotificacion(
                $dto->licenciaId,
                $dto->fechaNotificacion->toDateTimeString(),
            );

            return $actualizada;
        });
    }
}
