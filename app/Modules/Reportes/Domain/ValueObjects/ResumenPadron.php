<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

/**
 * Resumen ejecutivo del padrón: total por nivel de riesgo, tiempo promedio de atención
 * y cumplimiento del plazo TUPA (contra el plazo propio de cada procedimiento).
 */
final readonly class ResumenPadron
{
    /**
     * @param array<string, int> $porNivelRiesgo nombre del nivel => cantidad (incluye los niveles en cero)
     */
    public function __construct(
        public int $total,
        public array $porNivelRiesgo,
        public ?float $tiempoPromedioDiasHabiles,
        public int $dentroDePlazo,
        public ?float $porcentajeCumplimiento,
    ) {}

    /**
     * @param list<LicenciaPadron> $licencias
     * @param list<string> $nivelesRiesgo todos los niveles del catálogo, en orden
     */
    public static function de(array $licencias, array $nivelesRiesgo): self
    {
        $porNivel = array_fill_keys($nivelesRiesgo, 0);
        foreach ($licencias as $l) {
            $porNivel[$l->nivelRiesgo] = ($porNivel[$l->nivelRiesgo] ?? 0) + 1;
        }

        $total = count($licencias);
        $dentro = count(array_filter($licencias, fn (LicenciaPadron $l) => $l->dentroDePlazo()));
        $promedio = $total > 0
            ? round(array_sum(array_map(fn (LicenciaPadron $l) => $l->diasHabiles, $licencias)) / $total, 1)
            : null;

        return new self(
            total: $total,
            porNivelRiesgo: $porNivel,
            tiempoPromedioDiasHabiles: $promedio,
            dentroDePlazo: $dentro,
            porcentajeCumplimiento: $total > 0 ? round($dentro * 100 / $total, 1) : null,
        );
    }
}
