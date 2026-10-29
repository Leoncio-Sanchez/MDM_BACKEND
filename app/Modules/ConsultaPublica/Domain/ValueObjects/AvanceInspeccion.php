<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\ValueObjects;

use DateTimeImmutable;

/**
 * En qué va la inspección ITSE de un expediente, en lo que le sirve al ciudadano: cuándo
 * lo visitan y hasta cuándo puede subsanar. Sin nombre del inspector ni detalle del acta.
 */
final readonly class AvanceInspeccion
{
    /** Aún no se asigna inspector */
    public const POR_ASIGNAR = 'POR_ASIGNAR';

    /** Asignada, la fecha de visita se coordinará */
    public const POR_PROGRAMAR = 'POR_PROGRAMAR';

    public const PROGRAMADA = 'PROGRAMADA';

    /** Observada en la 1.ª visita: debe subsanar hasta la fecha, luego viene la reinspección */
    public const OBSERVADA = 'OBSERVADA';

    /** Aprobada: la oficina emite el certificado */
    public const APROBADA = 'APROBADA';

    /** Desaprobada en la reinspección: la oficina registrará la denegatoria */
    public const DESAPROBADA = 'DESAPROBADA';

    private function __construct(public string $estado, public ?DateTimeImmutable $fecha = null) {}

    /**
     * Solo mientras la ITSE del expediente sigue pendiente (sin certificado ni denegatoria).
     *
     * @param  string|null  $resultadoUltimaActa  APROBADO | OBSERVADO | DESAPROBADO (la última acta vigente)
     */
    public static function desde(
        bool $itsePendiente,
        ?string $resultadoUltimaActa,
        ?DateTimeImmutable $plazoSubsanacion,
        bool $asignada,
        ?DateTimeImmutable $visitaProgramada,
    ): ?self {
        if (! $itsePendiente) {
            return null;
        }

        return match ($resultadoUltimaActa) {
            'APROBADO' => new self(self::APROBADA),
            'DESAPROBADO' => new self(self::DESAPROBADA),
            'OBSERVADO' => new self(self::OBSERVADA, $plazoSubsanacion),
            default => match (true) {
                ! $asignada => new self(self::POR_ASIGNAR),
                $visitaProgramada !== null => new self(self::PROGRAMADA, $visitaProgramada),
                default => new self(self::POR_PROGRAMAR),
            },
        };
    }
}
