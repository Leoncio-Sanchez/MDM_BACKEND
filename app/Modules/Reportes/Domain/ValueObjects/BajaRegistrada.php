<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

/** Licencia o certificado ITSE pasado a un estado inactivo (anulada, revocada) — reporte para el OCI. */
final readonly class BajaRegistrada
{
    public function __construct(
        public string $fechaEstado,
        public string $estado,
        /** Resolución u otro documento que sustenta la baja */
        public string $documento,
        public string $motivo,
        /** N° de licencia o de certificado ITSE */
        public int $numero,
        public int $numeroExpediente,
        public string $nombreComercial,
        public string $titular,
        public string $documentoTitular,
        public string $direccion,
        /** Emisión de la licencia o expedición del certificado */
        public string $fechaEmision,
        public string $registradoPor,
        /** Cuándo se digitó la baja, en hora de Perú (Y-m-d H:i) */
        public string $fechaRegistro,
    ) {}
}
