<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class ListarGirosItseDTO
{
    public function __construct(
        public int $itseId,
    ) {}
}
