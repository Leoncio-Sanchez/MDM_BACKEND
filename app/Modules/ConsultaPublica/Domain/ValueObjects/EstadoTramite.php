<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\ValueObjects;

use DateTimeImmutable;

/** Estados que se muestran al ciudadano (textos simples, sin detalle interno). */
final class EstadoTramite
{
    public const EN_TRAMITE = 'EN TRÁMITE';
    public const ATENDIDO = 'ATENDIDO';
    public const IMPROCEDENTE = 'IMPROCEDENTE';
    public const VIGENTE = 'VIGENTE';
    public const VENCIDO = 'VENCIDO';
    public const INACTIVO = 'INACTIVO';

    public const ANULADO = 'ANULADO';

    /**
     * Misma regla que "expedientes pendientes": cada autorización que el procedimiento
     * requiere (LF / ITSE) queda resuelta al emitirse o al declararse improcedente.
     * Un expediente anulado deja de estar en trámite, sea cual sea su avance.
     */
    public static function deExpediente(
        bool $requiereLf,
        bool $requiereItse,
        bool $tieneLf,
        bool $tieneItse,
        bool $lfImprocedente,
        bool $itseImprocedente,
        bool $anulado = false,
    ): string {
        if ($anulado) {
            return self::ANULADO;
        }

        $lfPendiente = $requiereLf && ! $tieneLf && ! $lfImprocedente;
        $itsePendiente = $requiereItse && ! $tieneItse && ! $itseImprocedente;

        if ($lfPendiente || $itsePendiente) {
            return self::EN_TRAMITE;
        }
        if (! $tieneLf && ! $tieneItse && ($lfImprocedente || $itseImprocedente)) {
            return self::IMPROCEDENTE;
        }

        return self::ATENDIDO;
    }

    /** Licencia o certificado ITSE ya emitido. Sin fecha de vencimiento = vigencia indeterminada. */
    public static function deAutorizacion(bool $inactiva, ?DateTimeImmutable $vencimiento, DateTimeImmutable $hoy): string
    {
        if ($inactiva) {
            return self::INACTIVO;
        }
        if ($vencimiento !== null && $vencimiento < $hoy) {
            return self::VENCIDO;
        }

        return self::VIGENTE;
    }
}
