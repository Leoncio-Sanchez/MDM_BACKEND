<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\CrearExpedienteDTO;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteDuplicadoError;
use App\Modules\Expedientes\Domain\Exceptions\SolicitanteNoValido;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final readonly class CrearExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private TipoProcedimientoTupaReadRepositoryInterface $tipos,
        private PersonaReadRepositoryInterface $personas,
        private CalculadoraDiasHabilesService $calculadora,
    ) {}

    public function execute(CrearExpedienteDTO $dto): Expediente
    {
        return DB::transaction(function () use ($dto): Expediente {
            $tipo = $this->tipos->buscarPorId($dto->tipoProcedimientoTupaId);
            if (! $tipo) {
                throw TipoProcedimientoNoValido::noExiste($dto->tipoProcedimientoTupaId);
            }

            $solicitante = $this->personas->buscarPorId($dto->solicitanteId);
            if (! $solicitante) {
                throw SolicitanteNoValido::noExiste($dto->solicitanteId);
            }

            if ($dto->representanteId !== null) {
                $representante = $this->personas->buscarPorId($dto->representanteId);
                if (! $representante) {
                    throw SolicitanteNoValido::representanteNoExiste($dto->representanteId);
                }
            }

            $anio = (int) $dto->fechaRecepcion->format('Y');
            $numero = $dto->numeroExpediente
                ?? $this->expedientes->siguienteNumeroEnAnio($anio);

            if ($this->expedientes->existeNumeroEnAnio($numero, $anio)) {
                throw ExpedienteDuplicadoError::porNumeroYAnio($numero, $anio);
            }

            /** @var int $plazoDias */
            $plazoDias = (int) ($tipo->plazoAtencionDias ?? $tipo->plazo_atencion_dias ?? 0);
            /** @var int $diasAlerta */
            $diasAlerta = (int) ($tipo->diasAlertaVencimiento ?? $tipo->dias_alerta_vencimiento ?? 0);

            $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                $dto->fechaRecepcion,
                $plazoDias,
            );
            $fechaAlerta = $this->calculadora->calcularFechaAlerta(
                $fechaVencimiento,
                $diasAlerta,
            );

            $expediente = Expediente::crear(
                uuid: Uuid::uuid4()->toString(),
                numeroExpediente: $numero,
                fechaRecepcion: $dto->fechaRecepcion,
                tipoProcedimientoTupaId: $dto->tipoProcedimientoTupaId,
                solicitanteId: $dto->solicitanteId,
                usuarioId: $dto->usuarioId,
                fechaVencimiento: $fechaVencimiento,
                fechaAlerta: $fechaAlerta,
                representanteId: $dto->representanteId,
                observaciones: $dto->observaciones,
            );

            return $this->expedientes->crear($expediente);
        });
    }
}
