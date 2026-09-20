<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;

use Carbon\CarbonImmutable;

final readonly class ModificarLicenciaDTO
{
    public function __construct(
        public int $licenciaId,
        public int $numeroLicencia,
        public string $nombreComercial,
        public int $tipoLicenciaId,
        public int $titularId,
        public int $conductorId,
        public int $expedienteId,
        public int $nivelRiesgoId,
        public int $zonificacionId,
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
}
