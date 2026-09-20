<?php
declare(strict_types=1);

namespace App\Modules\Estados\Application\DTOs;

use App\Modules\Estados\Presentation\Requests\ListarEstadosRequest;

final readonly class ListarEstadosDTO
{
    public function __construct(
        public int $pagina,
        public int $porPagina,
    ) {}

    public static function fromRequest(ListarEstadosRequest $request): self
    {
        return new self(
            pagina: (int) $request->input('page', 1),
            porPagina: (int) $request->input('per_page', 10),
        );
    }
}
