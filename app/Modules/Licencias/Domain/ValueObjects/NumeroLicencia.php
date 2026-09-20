<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\ValueObjects;

use App\Modules\Licencias\Domain\Exceptions\NumeroLicenciaInvalido;

final readonly class NumeroLicencia
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw new NumeroLicenciaInvalido('El número de licencia no puede estar vacío');
        }
        if (strlen($value) > 50) {
            throw new NumeroLicenciaInvalido('El número excede 50 caracteres');
        }
        return new self($value);
    }
}
