<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\ValueObjects;

final readonly class DocumentoConcatenado
{
    private function __construct(public string $valor) {}

    public static function crear(?string $valor): self
    {
        return new self($valor !== null ? trim($valor) : '');
    }
}
