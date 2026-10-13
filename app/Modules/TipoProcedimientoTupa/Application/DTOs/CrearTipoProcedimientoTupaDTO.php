<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Application\DTOs;

final readonly class CrearTipoProcedimientoTupaDTO
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public int $plazoAtencionDias,
        public int $diasAlertaVencimiento,
        public bool $requiereLf,
        public bool $requiereItse,
        public int $unidadOrganicaId,
        public int $usuarioId,
        public bool $estaActivo = true
    ) {}
}
