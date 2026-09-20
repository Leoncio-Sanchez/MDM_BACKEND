<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Domain\Entities;

final readonly class TipoProcedimientoTupa
{
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public int $plazoAtencionDias,
        public int $diasAlertaVencimiento,
        public bool $estaActivo,
        public bool $requiereLf,
        public bool $requiereItse,
        public \DateTimeImmutable $fechaDigitacion,
        public int $unidadOrganicaId,
        public int $usuarioId
    ) {}

    public static function crear(
        string $codigo,
        string $nombre,
        int $plazoAtencionDias,
        int $diasAlertaVencimiento,
        bool $requiereLf,
        bool $requiereItse,
        int $unidadOrganicaId,
        int $usuarioId
    ): self {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            plazoAtencionDias: $plazoAtencionDias,
            diasAlertaVencimiento: $diasAlertaVencimiento,
            estaActivo: true,
            requiereLf: $requiereLf,
            requiereItse: $requiereItse,
            fechaDigitacion: new \DateTimeImmutable,
            unidadOrganicaId: $unidadOrganicaId,
            usuarioId: $usuarioId
        );
    }

    public function actualizar(
        ?string $codigo = null,
        ?string $nombre = null,
        ?int $plazoAtencionDias = null,
        ?int $diasAlertaVencimiento = null,
        ?bool $estaActivo = null,
        ?bool $requiereLf = null,
        ?bool $requiereItse = null,
        ?int $unidadOrganicaId = null
    ): self {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            plazoAtencionDias: $plazoAtencionDias ?? $this->plazoAtencionDias,
            diasAlertaVencimiento: $diasAlertaVencimiento ?? $this->diasAlertaVencimiento,
            estaActivo: $estaActivo ?? $this->estaActivo,
            requiereLf: $requiereLf ?? $this->requiereLf,
            requiereItse: $requiereItse ?? $this->requiereItse,
            fechaDigitacion: $this->fechaDigitacion,
            unidadOrganicaId: $unidadOrganicaId ?? $this->unidadOrganicaId,
            usuarioId: $this->usuarioId
        );
    }
}
