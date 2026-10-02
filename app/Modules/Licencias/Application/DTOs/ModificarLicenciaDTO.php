<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

use Carbon\CarbonImmutable;

final readonly class ModificarLicenciaDTO
{
    public function __construct(
        public int $licenciaId,
        public ?int $numeroLicencia = null,
        public string $nombreComercial = '',
        public int $tipoLicenciaId = 1,
        public int $titularId = 0,
        public int $conductorId = 0,
        public int $expedienteId = 0,
        public int $nivelRiesgoId = 0,
        public int $zonificacionId = 0,
        public ?int $licenciaPrincipalId = null,
        public bool $esVigenciaIndeterminada = true,
        public ?CarbonImmutable $fechaInicioVigencia = null,
        public ?CarbonImmutable $fechaFinVigencia = null,
        public string $actividad = '',
        public string $direccion = '',
        public int $horaDesde = 0,
        public int $horaHasta = 0,
        public string $resolucionNumero = '',
        public float $area = 0.0,
        public string $numeroReciboPago = '',
        public ?string $observaciones = null,
        public bool $sePuedePublicar = false,
        /** @var int[] */
        public array $giros = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            licenciaId: (int) ($data['licencia_id'] ?? 0),
            numeroLicencia: !empty($data['numero_licencia']) ? (int) $data['numero_licencia'] : null,
            nombreComercial: (string) ($data['nombre_comercial'] ?? ''),
            tipoLicenciaId: (int) ($data['tipo_licencia_id'] ?? 1),
            titularId: (int) ($data['titular_id'] ?? 0),
            conductorId: (int) ($data['conductor_id'] ?? $data['titular_id'] ?? 0),
            expedienteId: (int) ($data['expediente_id'] ?? 0),
            nivelRiesgoId: (int) ($data['nivel_riesgo_id'] ?? 0),
            zonificacionId: (int) ($data['zonificacion_id'] ?? 0),
            licenciaPrincipalId: isset($data['licencia_principal_id']) ? (int) $data['licencia_principal_id'] : null,
            esVigenciaIndeterminada: (bool) ($data['es_vigencia_indeterminada'] ?? true),
            fechaInicioVigencia: !empty($data['fecha_inicio_vigencia']) ? CarbonImmutable::parse($data['fecha_inicio_vigencia']) : null,
            fechaFinVigencia: !empty($data['fecha_fin_vigencia']) ? CarbonImmutable::parse($data['fecha_fin_vigencia']) : null,
            actividad: (string) ($data['actividad'] ?? ''),
            direccion: (string) ($data['direccion'] ?? ''),
            horaDesde: (int) ($data['hora_desde'] ?? 0),
            horaHasta: (int) ($data['hora_hasta'] ?? 0),
            resolucionNumero: (string) ($data['resolucion_numero'] ?? ''),
            area: (float) ($data['area'] ?? 0.0),
            numeroReciboPago: (string) ($data['numero_recibo_pago'] ?? ''),
            observaciones: isset($data['observaciones']) ? (string) $data['observaciones'] : null,
            sePuedePublicar: (bool) ($data['se_puede_publicar'] ?? false),
            giros: $data['giros'] ?? [],
        );
    }
}
