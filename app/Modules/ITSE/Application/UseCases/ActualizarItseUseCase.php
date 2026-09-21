<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\ActualizarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseInvalido;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
        private PersonaReadRepositoryInterface $personas,
        private NivelRiesgoReadRepositoryInterface $niveles,
    ) {}

    public function execute(ActualizarItseDTO $dto): Itse
    {
        return DB::transaction(function () use ($dto): Itse {
            $itse = $this->itses->buscarPorId($dto->id);
            if (!$itse) {
                throw ItseNoEncontrado::porId($dto->id);
            }

            if (!in_array($dto->tipoItseId, [1, 2], true)) {
                throw ItseInvalido::tipoInvalido($dto->tipoItseId);
            }

            if ($this->itses->existeNumero($dto->numeroItse, $dto->id)) {
                throw ItseInvalido::numeroDuplicado($dto->numeroItse);
            }

            if ($this->itses->existeRecibo($dto->numeroReciboPago, $dto->id)) {
                throw ItseInvalido::reciboDuplicado($dto->numeroReciboPago);
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

            $actualizado = $itse->actualizar(
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
                nivelRiesgoId: $dto->nivelRiesgoId,
                conductorId: $dto->conductorId,
                titularId: $dto->titularId,
            );

            return $this->itses->actualizar($actualizado);
        });
    }
}
