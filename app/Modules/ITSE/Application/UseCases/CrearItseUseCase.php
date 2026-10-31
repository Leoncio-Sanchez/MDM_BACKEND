<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\CrearItseDTO;
use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\InspeccionReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ExpedienteNoValidoItse;
use App\Modules\ITSE\Domain\Exceptions\ItseInvalido;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final readonly class CrearItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
        private ExpedienteReadRepositoryInterface $expedientes,
        private PersonaReadRepositoryInterface $personas,
        private NivelRiesgoReadRepositoryInterface $niveles,
        private InspeccionReadRepositoryInterface $inspecciones,
        private AutorizacionImprocedenteReadRepositoryInterface $improcedentes,
    ) {}

    public function execute(CrearItseDTO $dto): Itse
    {
        return DB::transaction(function () use ($dto): Itse {
            if (!in_array($dto->tipoItseId, [1, 2], true)) {
                throw ItseInvalido::tipoInvalido($dto->tipoItseId);
            }

            if ($this->itses->existeNumero($dto->numeroItse)) {
                throw ItseInvalido::numeroDuplicado($dto->numeroItse);
            }

            if ($this->itses->existeRecibo($dto->numeroReciboPago)) {
                throw ItseInvalido::reciboDuplicado($dto->numeroReciboPago);
            }

            if (!$this->expedientes->existe($dto->expedienteId)) {
                throw ExpedienteNoValidoItse::noExiste($dto->expedienteId);
            }

            if ($this->expedientes->estaAnulado($dto->expedienteId)) {
                throw ExpedienteNoValidoItse::anulado($dto->expedienteId);
            }

            // La denegatoria no se deshace: ese expediente ya no admite certificado
            if ($this->improcedentes->existe($dto->expedienteId, 'ITSE')) {
                throw ExpedienteNoValidoItse::denegado($dto->expedienteId);
            }

            // El certificado se sustenta en el acta Aprobada del inspector (D.S. N° 002-2018-PCM)
            $motivo = $this->inspecciones->situacion($dto->expedienteId)?->motivoParaNoEmitir();
            if ($motivo !== null) {
                throw ExpedienteNoValidoItse::sinActaAprobada($motivo);
            }

            if (!$this->personas->existe($dto->titularId)) {
                throw ItseInvalido::titularNoExiste($dto->titularId);
            }

            if (!$this->personas->existe($dto->conductorId)) {
                throw ItseInvalido::conductorNoExiste($dto->conductorId);
            }

            if (!$this->niveles->existe($dto->nivelRiesgoId)) {
                throw ItseInvalido::nivelRiesgoNoExiste($dto->nivelRiesgoId);
            }

            $itse = Itse::crear(
                uuid: Uuid::uuid4()->toString(),
                numeroItse: $dto->numeroItse,
                fechaExpedicion: $dto->fechaExpedicion,
                fechaSolicitudRenovacion: $dto->fechaSolicitudRenovacion,
                fechaCaducidad: $dto->fechaCaducidad,
                nombreComercial: $dto->nombreComercial,
                direccion: $dto->direccion,
                resolucionNumero: $dto->resolucionNumero,
                area: $dto->area,
                capacidadAforo: $dto->capacidadAforo,
                numeroReciboPago: $dto->numeroReciboPago,
                observaciones: $dto->observaciones,
                sePuedePublicar: $dto->sePuedePublicar,
                tipoItseId: $dto->tipoItseId,
                expedienteId: $dto->expedienteId,
                itsePrincipalId: $dto->itsePrincipalId,
                usuarioId: $dto->usuarioId,
                nivelRiesgoId: $dto->nivelRiesgoId,
                conductorId: $dto->conductorId,
                titularId: $dto->titularId,
                fechaDigitacion: CarbonImmutable::now(),
            );

            return $this->itses->crear($itse);
        });
    }
}
