<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\ActualizarExpedienteDTO;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteDuplicadoError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\SolicitanteNoValido;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private TipoProcedimientoTupaReadRepositoryInterface $tipos,
        private PersonaReadRepositoryInterface $personas,
        private CalculadoraDiasHabilesService $calculadora,
    ) {}

    public function execute(ActualizarExpedienteDTO $dto): Expediente
    {
        return DB::transaction(function () use ($dto): Expediente {
            $existente = $this->expedientes->buscarPorId($dto->expedienteId);
            if (! $existente) {
                throw ExpedienteNoEncontrado::porId($dto->expedienteId);
            }

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
            if ($this->expedientes->existeNumeroEnAnio($dto->numeroExpediente, $anio, $dto->expedienteId)) {
                throw ExpedienteDuplicadoError::porNumeroYAnio($dto->numeroExpediente, $anio);
            }

            /** @var int $plazoDias */
            $plazoDias = (int) ($tipo->plazoAtencionDias ?? $tipo->plazo_atencion_dias ?? 0);
            /** @var int $diasAlerta */
            $diasAlerta = (int) ($tipo->diasAlertaVencimiento ?? $tipo->dias_alerta_vencimiento ?? 0);

            // Recalcular SIEMPRE (mismo comportamiento que Django)
            $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                $dto->fechaRecepcion,
                $plazoDias,
            );

            // Si tiene ampliación, aplicarla sobre la fecha base
            if ($existente->diasAmpliacion !== null && $existente->diasAmpliacion > 0) {
                $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                    $fechaVencimiento,
                    $existente->diasAmpliacion,
                );
            }

            $fechaAlerta = $this->calculadora->calcularFechaAlerta(
                $fechaVencimiento,
                $diasAlerta,
            );

            $modificada = $existente->actualizar(
                tipoProcedimientoTupaId: $dto->tipoProcedimientoTupaId,
                numeroExpediente: $dto->numeroExpediente,
                fechaRecepcion: $dto->fechaRecepcion,
                solicitanteId: $dto->solicitanteId,
                representanteId: $dto->representanteId,
                observaciones: $dto->observaciones,
                fechaVencimiento: $fechaVencimiento,
                fechaAlerta: $fechaAlerta,
            );

            return $this->expedientes->actualizar($modificada);
        });
    }
}
