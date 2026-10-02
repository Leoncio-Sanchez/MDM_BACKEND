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
            numeroLicencia: (int) $dto->numeroLicencia,
            nombreComercial: $dto->nombreComercial,
            tipoLicenciaId: $dto->tipoLicenciaId,
            titularId: $dto->titularId,
            conductorId: $dto->conductorId,
            expedienteId: $dto->expedienteId,
            nivelRiesgoId: $dto->nivelRiesgoId,
            zonificacionId: $dto->zonificacionId,
            licenciaPrincipalId: $dto->licenciaPrincipalId,
            esVigenciaIndeterminada: $dto->esVigenciaIndeterminada,
            fechaInicioVigencia: $dto->fechaInicioVigencia,
            fechaFinVigencia: $dto->fechaFinVigencia,
            actividad: $dto->actividad,
            direccion: $dto->direccion,
            horaDesde: $dto->horaDesde,
            horaHasta: $dto->horaHasta,
            resolucionNumero: $dto->resolucionNumero,
            area: $dto->area,
            numeroReciboPago: $dto->numeroReciboPago,
            observaciones: $dto->observaciones,
            sePuedePublicar: $dto->sePuedePublicar,
        );
        return $this->licencias->crear($licencia);
    }
}