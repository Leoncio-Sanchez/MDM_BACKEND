<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\DTOs;

use App\Modules\Estados\Presentation\Requests\ActualizarEstadoRequest;

final readonly class ActualizarEstadoDTO
{
    public function __construct(
        public int $id,
        public ?string $nombre,
        public ?bool $esParaLf,
        public ?bool $esParaItse,
        public ?bool $estaActivo,
    ) {}

    public static function fromRequest(ActualizarEstadoRequest $request, int $id): self
    {
        return new self(
            id: $id,
            nombre: $request->validated('nombre'),
            esParaLf: $request->has('es_para_lf') ? (bool) $request->input('es_para_lf') : null,
            esParaItse: $request->has('es_para_itse') ? (bool) $request->input('es_para_itse') : null,
            estaActivo: $request->has('esta_activo') ? (bool) $request->input('esta_activo') : null,
        );
    }
}
