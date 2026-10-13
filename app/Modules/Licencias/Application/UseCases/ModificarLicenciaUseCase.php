<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\UseCases;

use App\Modules\Licencias\Application\DTOs\ModificarLicenciaDTO;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaGiroRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDuplicadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\ReciboPagoDuplicadoError;
use Illuminate\Support\Facades\DB;

final readonly class ModificarLicenciaUseCase
{
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
        private LicenciaGiroRepositoryInterface $giros,
        private ExpedienteReadRepositoryInterface $expedientes,
    ) {}

    public function execute(ModificarLicenciaDTO $dto): Licencia
    {
        return DB::transaction(function () use ($dto) {
            $existente = $this->licencias->buscarPorId($dto->licenciaId);
            if (!$existente) {
                throw LicenciaNoEncontrada::porId($dto->licenciaId);
            }

            $numeroLicencia = $dto->numeroLicencia ?? $existente->numeroLicencia->toInt();
            $expedienteId = $dto->expedienteId ?: $existente->expedienteId;

            if ($this->expedientes->tieneAutorizacionImprocedente($expedienteId, 'LF')) {
                throw LicenciaDenegadaError::porExpediente($expedienteId);
            }

            if ($this->licencias->existeNumeroLicencia($numeroLicencia, $dto->licenciaId)) {
                throw LicenciaDuplicadaError::porNumero((string) $numeroLicencia);
            }

            if ($dto->numeroReciboPago !== '' && $this->licencias->existeReciboPago($dto->numeroReciboPago, $dto->licenciaId)) {
                throw ReciboPagoDuplicadoError::porNumero($dto->numeroReciboPago);
            }

            $modificada = $existente->actualizar(
                numeroLicencia: $numeroLicencia,
                nombreComercial: $dto->nombreComercial,
                titularId: $dto->titularId,
                conductorId: $dto->conductorId,
                licenciaPrincipalId: $dto->licenciaPrincipalId,
                expedienteId: $dto->expedienteId,
                tipoLicenciaId: $dto->tipoLicenciaId,
                nivelRiesgoId: $dto->nivelRiesgoId,
                zonificacionId: $dto->zonificacionId,
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

            $persistida = $this->licencias->actualizar($modificada);

            $this->giros->eliminarGiros($dto->licenciaId);
            if (!empty($dto->giros)) {
                $this->giros->insertarGiros($dto->licenciaId, $dto->giros, $dto->usuarioId);
            }

            return $persistida;
        });
    }
}
