<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Entities;

use DateTimeImmutable;

final readonly class Expediente
{
    /**
     * @param array<int, AutorizacionImprocedente> $autorizacionesImprocedentes
     */
    public function __construct(
        public ?int $id,
        public string $uuid,
        public int $numeroExpediente,
        public DateTimeImmutable $fechaRecepcion,
        public int $tipoProcedimientoTupaId,
        public int $solicitanteId,
        public ?int $representanteId,
        public ?string $observaciones,
        public DateTimeImmutable $fechaVencimiento,
        public DateTimeImmutable $fechaAlerta,
        public ?DateTimeImmutable $fechaSuspension,
        public ?int $diasAmpliacion,
        public ?string $motivoAmpliacion,
        public ?int $usuarioAmpliacionId,
        public ?DateTimeImmutable $fechaDigitacionAmpliacion,
        public int $usuarioId,
        public DateTimeImmutable $fechaDigitacion,
        public array $autorizacionesImprocedentes = [],
    ) {}

    public static function crear(
        string $uuid,
        int $numeroExpediente,
        DateTimeImmutable $fechaRecepcion,
        int $tipoProcedimientoTupaId,
        int $solicitanteId,
        int $usuarioId,
        DateTimeImmutable $fechaVencimiento,
        DateTimeImmutable $fechaAlerta,
        ?int $representanteId = null,
        ?string $observaciones = null,
    ): self {
        $now = new DateTimeImmutable();
        return new self(
            id: null,
            uuid: $uuid,
            numeroExpediente: $numeroExpediente,
            fechaRecepcion: $fechaRecepcion,
            tipoProcedimientoTupaId: $tipoProcedimientoTupaId,
            solicitanteId: $solicitanteId,
            representanteId: $representanteId,
            observaciones: $observaciones,
            fechaVencimiento: $fechaVencimiento,
            fechaAlerta: $fechaAlerta,
            fechaSuspension: null,
            diasAmpliacion: null,
            motivoAmpliacion: null,
            usuarioAmpliacionId: null,
            fechaDigitacionAmpliacion: null,
            usuarioId: $usuarioId,
            fechaDigitacion: $now,
            autorizacionesImprocedentes: [],
        );
    }

    public function actualizar(
        int $tipoProcedimientoTupaId,
        int $numeroExpediente,
        DateTimeImmutable $fechaRecepcion,
        int $solicitanteId,
        ?int $representanteId,
        ?string $observaciones,
        DateTimeImmutable $fechaVencimiento,
        DateTimeImmutable $fechaAlerta,
    ): self {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroExpediente: $numeroExpediente,
            fechaRecepcion: $fechaRecepcion,
            tipoProcedimientoTupaId: $tipoProcedimientoTupaId,
            solicitanteId: $solicitanteId,
            representanteId: $representanteId,
            observaciones: $observaciones,
            fechaVencimiento: $fechaVencimiento,
            fechaAlerta: $fechaAlerta,
            fechaSuspension: $this->fechaSuspension,
            diasAmpliacion: $this->diasAmpliacion,
            motivoAmpliacion: $this->motivoAmpliacion,
            usuarioAmpliacionId: $this->usuarioAmpliacionId,
            fechaDigitacionAmpliacion: $this->fechaDigitacionAmpliacion,
            usuarioId: $this->usuarioId,
            fechaDigitacion: $this->fechaDigitacion,
            autorizacionesImprocedentes: $this->autorizacionesImprocedentes,
        );
    }

    public function ampliarPlazo(
        int $dias,
        string $motivo,
        int $usuarioId,
        DateTimeImmutable $nuevaFechaVencimiento,
        DateTimeImmutable $nuevaFechaAlerta,
    ): self {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroExpediente: $this->numeroExpediente,
            fechaRecepcion: $this->fechaRecepcion,
            tipoProcedimientoTupaId: $this->tipoProcedimientoTupaId,
            solicitanteId: $this->solicitanteId,
            representanteId: $this->representanteId,
            observaciones: $this->observaciones,
            fechaVencimiento: $nuevaFechaVencimiento,
            fechaAlerta: $nuevaFechaAlerta,
            fechaSuspension: $this->fechaSuspension,
            diasAmpliacion: $dias,
            motivoAmpliacion: $motivo,
            usuarioAmpliacionId: $usuarioId,
            fechaDigitacionAmpliacion: new DateTimeImmutable(),
            usuarioId: $this->usuarioId,
            fechaDigitacion: $this->fechaDigitacion,
            autorizacionesImprocedentes: $this->autorizacionesImprocedentes,
        );
    }

    /**
     * @param array<int, AutorizacionImprocedente> $autorizaciones
     */
    public function conAutorizaciones(array $autorizaciones): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroExpediente: $this->numeroExpediente,
            fechaRecepcion: $this->fechaRecepcion,
            tipoProcedimientoTupaId: $this->tipoProcedimientoTupaId,
            solicitanteId: $this->solicitanteId,
            representanteId: $this->representanteId,
            observaciones: $this->observaciones,
            fechaVencimiento: $this->fechaVencimiento,
            fechaAlerta: $this->fechaAlerta,
            fechaSuspension: $this->fechaSuspension,
            diasAmpliacion: $this->diasAmpliacion,
            motivoAmpliacion: $this->motivoAmpliacion,
            usuarioAmpliacionId: $this->usuarioAmpliacionId,
            fechaDigitacionAmpliacion: $this->fechaDigitacionAmpliacion,
            usuarioId: $this->usuarioId,
            fechaDigitacion: $this->fechaDigitacion,
            autorizacionesImprocedentes: $autorizaciones,
        );
    }
}
