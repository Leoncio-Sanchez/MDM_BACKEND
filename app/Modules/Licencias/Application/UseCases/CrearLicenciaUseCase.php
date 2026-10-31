<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDuplicadaError;
use App\Modules\Licencias\Domain\Exceptions\ReciboPagoDuplicadoError;
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
            if ($this->expedientes->estaAnulado($dto->expedienteId)) {
                throw ExpedienteNoValido::anulado($dto->expedienteId);
            }
            // La denegatoria no se deshace: ese expediente ya no admite licencia
            if ($this->expedientes->tieneAutorizacionImprocedente($dto->expedienteId, 'LF')) {
                throw LicenciaDenegadaError::porExpediente($dto->expedienteId);
            }
        }

        // Validar antes de insertar: si no, la BD rechaza con un 500 (restricción UNIQUE)
        if ($this->licencias->existeNumeroLicencia((int) $dto->numeroLicencia)) {
            throw LicenciaDuplicadaError::porNumero((string) $dto->numeroLicencia);
        }
        if ($dto->numeroReciboPago !== '' && $this->licencias->existeReciboPago($dto->numeroReciboPago)) {
            throw ReciboPagoDuplicadoError::porNumero($dto->numeroReciboPago);
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
        return $this->licencias->crear($licencia, $dto->usuarioId);
    }
}