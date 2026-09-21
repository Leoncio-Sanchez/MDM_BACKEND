<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Entities;

use App\Modules\ITSE\Domain\Exceptions\ItseFechaInvalida;
use DateTimeImmutable;

final readonly class Itse
{
    public function __construct(
        public ?int $id,
        public string $uuid,
        public int $numeroItse,
        public DateTimeImmutable $fechaExpedicion,
        public ?DateTimeImmutable $fechaSolicitudRenovacion,
        public DateTimeImmutable $fechaCaducidad,
        public string $nombreComercial,
        public string $direccion,
        public string $resolucionNumero,
        public float $area,
        public int $capacidadAforo,
        public string $numeroReciboPago,
        public ?string $observaciones,
        public bool $sePuedePublicar,
        public ?DateTimeImmutable $fechaNotificacion,
        public DateTimeImmutable $fechaDigitacion,
        public int $tipoItseId,
        public int $expedienteId,
        public ?int $itsePrincipalId,
        public int $usuarioId,
        public int $nivelRiesgoId,
        public int $conductorId,
        public int $titularId,
        /** @var array<int, mixed> */
        public array $archivos = [],
        /** @var array<int, array<string, mixed>> */
        public array $historialEstados = [],
        /** @var array<int, array<string, mixed>> */
        public array $giros = [],
    ) {
        if ($fechaCaducidad < $fechaExpedicion) {
            throw ItseFechaInvalida::caducidadAntesDeExpedicion();
        }
    }

    public static function crear(
        string $uuid,
        int $numeroItse,
        DateTimeImmutable $fechaExpedicion,
        ?DateTimeImmutable $fechaSolicitudRenovacion,
        DateTimeImmutable $fechaCaducidad,
        string $nombreComercial,
        string $direccion,
        string $resolucionNumero,
        float $area,
        int $capacidadAforo,
        string $numeroReciboPago,
        ?string $observaciones,
        bool $sePuedePublicar,
        int $tipoItseId,
        int $expedienteId,
        ?int $itsePrincipalId,
        int $usuarioId,
        int $nivelRiesgoId,
        int $conductorId,
        int $titularId,
        DateTimeImmutable $fechaDigitacion,
    ): self {
        return new self(
            id: null,
            uuid: $uuid,
            numeroItse: $numeroItse,
            fechaExpedicion: $fechaExpedicion,
            fechaSolicitudRenovacion: $fechaSolicitudRenovacion,
            fechaCaducidad: $fechaCaducidad,
            nombreComercial: $nombreComercial,
            direccion: $direccion,
            resolucionNumero: $resolucionNumero,
            area: $area,
            capacidadAforo: $capacidadAforo,
            numeroReciboPago: $numeroReciboPago,
            observaciones: $observaciones,
            sePuedePublicar: $sePuedePublicar,
            fechaNotificacion: null,
            fechaDigitacion: $fechaDigitacion,
            tipoItseId: $tipoItseId,
            expedienteId: $expedienteId,
            itsePrincipalId: $itsePrincipalId,
            usuarioId: $usuarioId,
            nivelRiesgoId: $nivelRiesgoId,
            conductorId: $conductorId,
            titularId: $titularId,
        );
    }

    public function actualizar(
        DateTimeImmutable $fechaExpedicion,
        ?DateTimeImmutable $fechaSolicitudRenovacion,
        DateTimeImmutable $fechaCaducidad,
        string $nombreComercial,
        string $direccion,
        string $resolucionNumero,
        float $area,
        int $capacidadAforo,
        string $numeroReciboPago,
        ?string $observaciones,
        bool $sePuedePublicar,
        int $tipoItseId,
        int $nivelRiesgoId,
        int $conductorId,
        int $titularId,
    ): self {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroItse: $this->numeroItse,
            fechaExpedicion: $fechaExpedicion,
            fechaSolicitudRenovacion: $fechaSolicitudRenovacion,
            fechaCaducidad: $fechaCaducidad,
            nombreComercial: $nombreComercial,
            direccion: $direccion,
            resolucionNumero: $resolucionNumero,
            area: $area,
            capacidadAforo: $capacidadAforo,
            numeroReciboPago: $numeroReciboPago,
            observaciones: $observaciones,
            sePuedePublicar: $sePuedePublicar,
            fechaNotificacion: $this->fechaNotificacion,
            fechaDigitacion: $this->fechaDigitacion,
            tipoItseId: $tipoItseId,
            expedienteId: $this->expedienteId,
            itsePrincipalId: $this->itsePrincipalId,
            usuarioId: $this->usuarioId,
            nivelRiesgoId: $nivelRiesgoId,
            conductorId: $conductorId,
            titularId: $titularId,
        );
    }

    public function conFechaNotificacion(DateTimeImmutable $fecha): self
    {
        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroItse: $this->numeroItse,
            fechaExpedicion: $this->fechaExpedicion,
            fechaSolicitudRenovacion: $this->fechaSolicitudRenovacion,
            fechaCaducidad: $this->fechaCaducidad,
            nombreComercial: $this->nombreComercial,
            direccion: $this->direccion,
            resolucionNumero: $this->resolucionNumero,
            area: $this->area,
            capacidadAforo: $this->capacidadAforo,
            numeroReciboPago: $this->numeroReciboPago,
            observaciones: $this->observaciones,
            sePuedePublicar: $this->sePuedePublicar,
            fechaNotificacion: $fecha,
            fechaDigitacion: $this->fechaDigitacion,
            tipoItseId: $this->tipoItseId,
            expedienteId: $this->expedienteId,
            itsePrincipalId: $this->itsePrincipalId,
            usuarioId: $this->usuarioId,
            nivelRiesgoId: $this->nivelRiesgoId,
            conductorId: $this->conductorId,
            titularId: $this->titularId,
        );
    }
}
