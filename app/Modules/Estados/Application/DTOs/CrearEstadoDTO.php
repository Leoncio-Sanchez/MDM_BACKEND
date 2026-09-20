<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\DTOs;

use App\Modules\Estados\Presentation\Requests\CrearEstadoRequest;

final readonly class CrearEstadoDTO
{
    public function __construct(
        public string $nombre,
        public bool $esParaLf,
        public bool $esParaItse,
    ) {}

    public static function fromRequest(CrearEstadoRequest $request): self
    {
        return new self(
            nombre: $request->validated('nombre'),
            esParaLf: (bool) $request->input('es_para_lf', true),
            esParaItse: (bool) $request->input('es_para_itse', true),
        );
    }
}
