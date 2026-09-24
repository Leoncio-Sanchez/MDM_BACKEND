<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Application\DTOs;

final readonly class ObtenerUsuarioDTO
{
    public function __construct(public int $userId) {}
}
