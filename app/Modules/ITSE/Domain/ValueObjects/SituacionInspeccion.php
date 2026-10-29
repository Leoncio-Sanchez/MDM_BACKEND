<?php

declare(strict_types=1);

namespace App\Modules\ITSE\Domain\ValueObjects;

/**
 * Lo que dicen las actas de inspección de un expediente sobre emitir su certificado ITSE.
 * Solo se cuentan las actas vigentes (las anuladas no sustentan nada).
 */
final readonly class SituacionInspeccion
{
    public function __construct(
        public int $numeroExpediente,
        /** El procedimiento requiere ITSE y el expediente se recibió desde que se exige el acta */
        public bool $exigeActa,
        public ?int $ultimaActaId,
        public ?string $ultimaActaNumero,
        /** APROBADO, OBSERVADO o DESAPROBADO */
        public ?string $ultimaActaResultado,
        public ?string $fechaVisita,
        public ?float $areaM2,
        public ?int $aforo,
    ) {}

    public function tieneActaAprobada(): bool
    {
        return $this->ultimaActaResultado === 'APROBADO';
    }

    public function permiteEmitir(): bool
    {
        return ! $this->exigeActa || $this->tieneActaAprobada();
    }

    /** Por qué no se puede emitir todavía (null si se puede) */
    public function motivoParaNoEmitir(): ?string
    {
        if ($this->permiteEmitir()) {
            return null;
        }

        return match ($this->ultimaActaResultado) {
            'OBSERVADO' => "El acta de inspección N° {$this->ultimaActaNumero} del expediente N° {$this->numeroExpediente} está Observada: el certificado ITSE se emite cuando la reinspección resulte Aprobada.",
            'DESAPROBADO' => "El expediente N° {$this->numeroExpediente} fue Desaprobado en la reinspección (acta N° {$this->ultimaActaNumero}): no corresponde el certificado, sino registrar la denegatoria del ITSE.",
            default => "El expediente N° {$this->numeroExpediente} aún no tiene acta de inspección Aprobada: el certificado ITSE se emite después de que el inspector registre el acta de su visita.",
        };
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'numero_expediente' => $this->numeroExpediente,
            'exige_acta' => $this->exigeActa,
            'se_puede_emitir' => $this->permiteEmitir(),
            'mensaje' => $this->motivoParaNoEmitir(),
            'acta' => $this->ultimaActaId === null ? null : [
                'id' => $this->ultimaActaId,
                'numero_acta' => $this->ultimaActaNumero,
                'resultado' => $this->ultimaActaResultado,
                'fecha_visita' => $this->fechaVisita,
                'area_m2' => $this->areaM2,
                'aforo' => $this->aforo,
            ],
        ];
    }
}
