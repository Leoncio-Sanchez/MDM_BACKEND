<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\AmpliarPlazoExpedienteDTO;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\Exceptions\TipoProcedimientoNoValido;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use DateTimeImmutable;

final readonly class AmpliarPlazoExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private TipoProcedimientoTupaReadRepositoryInterface $tiposTupa,
        private CalculadoraDiasHabilesService $calculadora,
    ) {}

    public function execute(AmpliarPlazoExpedienteDTO $dto): Expediente
    {
        $expediente = $this->expedientes->buscarPorId($dto->expedienteId);
        if ($expediente === null) {
            throw ExpedienteNoEncontrado::porId($dto->expedienteId);
        }
        $expediente->asegurarQueNoEstaAnulado();

        $tipo = $this->tiposTupa->buscarPorId($expediente->tipoProcedimientoTupaId);
        if ($tipo === null) {
            throw TipoProcedimientoNoValido::porId($expediente->tipoProcedimientoTupaId);
        }

        $diasAlerta = (int) ($tipo->dias_alerta_vencimiento ?? 0);

        // Avanzar días hábiles desde fecha_vencimiento actual
        $nuevaFechaVencimiento = $this->calculadora->calcularFechaVencimiento(
            $expediente->fechaVencimiento,
            $dto->diasAmpliacion,
        );

        // Retroceder días hábiles desde nueva fecha de vencimiento
        $nuevaFechaAlerta = $this->calculadora->calcularFechaAlerta(
            $nuevaFechaVencimiento,
            $diasAlerta,
        );

        $expedienteActualizado = $expediente->conAmpliacion(
            fechaSuspension: $dto->fechaSuspension,
            diasAmpliacion: $dto->diasAmpliacion,
            motivoAmpliacion: $dto->motivoAmpliacion,
            fechaVencimiento: $nuevaFechaVencimiento,
            fechaAlerta: $nuevaFechaAlerta,
            usuarioAmpliacionId: $dto->usuarioId,
            fechaDigitacionAmpliacion: new DateTimeImmutable,
        );

        return $this->expedientes->actualizar($expedienteActualizado);
    }
}
