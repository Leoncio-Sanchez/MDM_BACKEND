<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use Ramsey\Uuid\Uuid;
final readonly class CrearLicenciaUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
        private ExpedienteReadRepositoryInterface $expedientes,
    ) {}
    public function execute(CrearLicenciaDTO $dto): Licencia {
        if ($dto->expedienteId !== null) {
            $expediente = $this->expedientes->buscarPorId($dto->expedienteId);
            if ($expediente === null) {
                throw ExpedienteNoValido::porId($dto->expedienteId);
            }
        }
        $licencia = Licencia::crear(
            uuid: Uuid::uuid4()->toString(),
            numeroLicencia: $dto->numeroLicencia,
            nombreComercial: $dto->nombreComercial,
            tipoLicenciaId: $dto->tipoLicenciaId,
            titularId: $dto->titularId,
            expedienteId: $dto->expedienteId,
            nivelRiesgoId: $dto->nivelRiesgoId,
            zonificacionId: $dto->zonificacionId,
        );
        return $this->licencias->crear($licencia);
    }
}