<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\ValueObjects;

use App\Modules\Personas\Domain\Exceptions\DocumentoInvalido;

final readonly class NumeroDocumento
{
    private function __construct(public string $valor) {}

    public static function crear(string $valor): self
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw DocumentoInvalido::vacio();
        }
        return new self($valor);
    }
}
