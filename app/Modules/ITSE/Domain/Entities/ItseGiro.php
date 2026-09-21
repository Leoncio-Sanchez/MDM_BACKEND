<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Entities;

final readonly class ItseGiro
{
    public function __construct(
        public int $id,
        public int $itseId,
        public int $giroId,
        public int $ciiuId,
        public string $nombre,
    ) {}
}
