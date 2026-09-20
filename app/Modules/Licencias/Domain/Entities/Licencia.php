<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Entities;

use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Carbon\CarbonImmutable;

final readonly class Licencia
{
    public function __construct(
        public ?int $id,
        public string $uuid,
        public NumeroLicencia $numeroLicencia,
        public CarbonImmutable $fechaEmision,
        public NombreComercial $nombreComercial,
        public int $titularId,
        public int $conductorId,
        public ?int $licenciaPrincipalId,
        public int $expedienteId,
        public int $tipoLicenciaId,
        public int $nivelRiesgoId,
        public int $zonificacionId,
        public bool $esVigenciaIndeterminada,
        public ?CarbonImmutable $fechaInicioVigencia,
        public ?CarbonImmutable $fechaFinVigencia,
        public string $actividad,
        public string $direccion,
        public int $horaDesde,
        public int $horaHasta,
        public string $resolucionNumero,
        public float $area,
        public string $numeroReciboPago,
        public ?string $observaciones,
        public bool $sePuedePublicar,
        public ?CarbonImmutable $fechaNotificacion,
        public EstadoLicencia $estado,
    ) {}

    public static function crear(
        string $uuid,
        int $numeroLicencia,
        string $nombreComercial,
        int $tipoLicenciaId,
        int $titularId,
        int $conductorId,
        int $expedienteId,
        int $nivelRiesgoId,
        int $zonificacionId,
        ?int $licenciaPrincipalId = null,
        bool $esVigenciaIndeterminada = true,
        ?CarbonImmutable $fechaInicioVigencia = null,
        ?CarbonImmutable $fechaFinVigencia = null,
        string $actividad = '',
        string $direccion = '',
        int $horaDesde = 0,
        int $horaHasta = 0,
        string $resolucionNumero = '',
        float $area = 0.0,
        string $numeroReciboPago = '',
        ?string $observaciones = null,
        bool $sePuedePublicar = false,
    ): self {
        if ($esVigenciaIndeterminada) {
            $fechaInicioVigencia = null;
            $fechaFinVigencia = null;
        }

        return new self(
            id: null,
            uuid: $uuid,
            numeroLicencia: NumeroLicencia::fromInt($numeroLicencia),
            fechaEmision: CarbonImmutable::now(),
            nombreComercial: NombreComercial::fromString($nombreComercial),
            titularId: $titularId,
            conductorId: $conductorId,
            licenciaPrincipalId: $licenciaPrincipalId,
            expedienteId: $expedienteId,
            tipoLicenciaId: $tipoLicenciaId,
            nivelRiesgoId: $nivelRiesgoId,
            zonificacionId: $zonificacionId,
            esVigenciaIndeterminada: $esVigenciaIndeterminada,
            fechaInicioVigencia: $fechaInicioVigencia,
            fechaFinVigencia: $fechaFinVigencia,
            actividad: $actividad,
            direccion: $direccion,
            horaDesde: $horaDesde,
            horaHasta: $horaHasta,
            resolucionNumero: $resolucionNumero,
            area: $area,
            numeroReciboPago: $numeroReciboPago,
            observaciones: $observaciones,
            sePuedePublicar: $sePuedePublicar,
            fechaNotificacion: null,
            estado: EstadoLicencia::registrada(),
        );
    }

    public function actualizar(
        ?int $numeroLicencia = null,
        ?string $nombreComercial = null,
        ?int $titularId = null,
        ?int $conductorId = null,
        ?int $licenciaPrincipalId = null,
        ?int $expedienteId = null,
        ?int $tipoLicenciaId = null,
        ?int $nivelRiesgoId = null,
        ?int $zonificacionId = null,
        ?bool $esVigenciaIndeterminada = null,
        ?CarbonImmutable $fechaInicioVigencia = null,
        ?CarbonImmutable $fechaFinVigencia = null,
        ?string $actividad = null,
        ?string $direccion = null,
        ?int $horaDesde = null,
        ?int $horaHasta = null,
        ?string $resolucionNumero = null,
        ?float $area = null,
        ?string $numeroReciboPago = null,
        ?string $observaciones = null,
        ?bool $sePuedePublicar = null,
    ): self {
        $esIndeterminada = $esVigenciaIndeterminada ?? $this->esVigenciaIndeterminada;

        return new self(
            id: $this->id,
            uuid: $this->uuid,
            numeroLicencia: $numeroLicencia !== null ? NumeroLicencia::fromInt($numeroLicencia) : $this->numeroLicencia,
            fechaEmision: $this->fechaEmision,
            nombreComercial: $nombreComercial !== null ? NombreComercial::fromString($nombreComercial) : $this->nombreComercial,
            titularId: $titularId ?? $this->titularId,
            conductorId: $conductorId ?? $this->conductorId,
            licenciaPrincipalId: $licenciaPrincipalId ?? $this->licenciaPrincipalId,
            expedienteId: $expedienteId ?? $this->expedienteId,
            tipoLicenciaId: $tipoLicenciaId ?? $this->tipoLicenciaId,
            nivelRiesgoId: $nivelRiesgoId ?? $this->nivelRiesgoId,
            zonificacionId: $zonificacionId ?? $this->zonificacionId,
            esVigenciaIndeterminada: $esIndeterminada,
            fechaInicioVigencia: $esIndeterminada ? null : ($fechaInicioVigencia ?? $this->fechaInicioVigencia),
            fechaFinVigencia: $esIndeterminada ? null : ($fechaFinVigencia ?? $this->fechaFinVigencia),
            actividad: $actividad ?? $this->actividad,
            direccion: $direccion ?? $this->direccion,
            horaDesde: $horaDesde ?? $this->horaDesde,
            horaHasta: $horaHasta ?? $this->horaHasta,
            resolucionNumero: $resolucionNumero ?? $this->resolucionNumero,
            area: $area ?? $this->area,
            numeroReciboPago: $numeroReciboPago ?? $this->numeroReciboPago,
            observaciones: $observaciones ?? $this->observaciones,
            sePuedePublicar: $sePuedePublicar ?? $this->sePuedePublicar,
            fechaNotificacion: $this->fechaNotificacion,
            estado: $this->estado,
        );
    }

    public function inactivar(): self
    {
        return $this->clone(['estado' => EstadoLicencia::inactiva()]);
    }

    public function conFechaNotificacion(CarbonImmutable $fecha): self
    {
        return $this->clone(['fechaNotificacion' => $fecha]);
    }

    /** @param array<string, mixed> $overrides */
    private function clone(array $overrides): self
    {
        return new self(
            id: array_key_exists('id', $overrides) ? $overrides['id'] : $this->id,
            uuid: array_key_exists('uuid', $overrides) ? $overrides['uuid'] : $this->uuid,
            numeroLicencia: array_key_exists('numeroLicencia', $overrides) ? $overrides['numeroLicencia'] : $this->numeroLicencia,
            fechaEmision: array_key_exists('fechaEmision', $overrides) ? $overrides['fechaEmision'] : $this->fechaEmision,
            nombreComercial: array_key_exists('nombreComercial', $overrides) ? $overrides['nombreComercial'] : $this->nombreComercial,
            titularId: array_key_exists('titularId', $overrides) ? $overrides['titularId'] : $this->titularId,
            conductorId: array_key_exists('conductorId', $overrides) ? $overrides['conductorId'] : $this->conductorId,
            licenciaPrincipalId: array_key_exists('licenciaPrincipalId', $overrides) ? $overrides['licenciaPrincipalId'] : $this->licenciaPrincipalId,
            expedienteId: array_key_exists('expedienteId', $overrides) ? $overrides['expedienteId'] : $this->expedienteId,
            tipoLicenciaId: array_key_exists('tipoLicenciaId', $overrides) ? $overrides['tipoLicenciaId'] : $this->tipoLicenciaId,
            nivelRiesgoId: array_key_exists('nivelRiesgoId', $overrides) ? $overrides['nivelRiesgoId'] : $this->nivelRiesgoId,
            zonificacionId: array_key_exists('zonificacionId', $overrides) ? $overrides['zonificacionId'] : $this->zonificacionId,
            esVigenciaIndeterminada: array_key_exists('esVigenciaIndeterminada', $overrides) ? $overrides['esVigenciaIndeterminada'] : $this->esVigenciaIndeterminada,
            fechaInicioVigencia: array_key_exists('fechaInicioVigencia', $overrides) ? $overrides['fechaInicioVigencia'] : $this->fechaInicioVigencia,
            fechaFinVigencia: array_key_exists('fechaFinVigencia', $overrides) ? $overrides['fechaFinVigencia'] : $this->fechaFinVigencia,
            actividad: array_key_exists('actividad', $overrides) ? $overrides['actividad'] : $this->actividad,
            direccion: array_key_exists('direccion', $overrides) ? $overrides['direccion'] : $this->direccion,
            horaDesde: array_key_exists('horaDesde', $overrides) ? $overrides['horaDesde'] : $this->horaDesde,
            horaHasta: array_key_exists('horaHasta', $overrides) ? $overrides['horaHasta'] : $this->horaHasta,
            resolucionNumero: array_key_exists('resolucionNumero', $overrides) ? $overrides['resolucionNumero'] : $this->resolucionNumero,
            area: array_key_exists('area', $overrides) ? $overrides['area'] : $this->area,
            numeroReciboPago: array_key_exists('numeroReciboPago', $overrides) ? $overrides['numeroReciboPago'] : $this->numeroReciboPago,
            observaciones: array_key_exists('observaciones', $overrides) ? $overrides['observaciones'] : $this->observaciones,
            sePuedePublicar: array_key_exists('sePuedePublicar', $overrides) ? $overrides['sePuedePublicar'] : $this->sePuedePublicar,
            fechaNotificacion: array_key_exists('fechaNotificacion', $overrides) ? $overrides['fechaNotificacion'] : $this->fechaNotificacion,
            estado: array_key_exists('estado', $overrides) ? $overrides['estado'] : $this->estado,
        );
    }
}
