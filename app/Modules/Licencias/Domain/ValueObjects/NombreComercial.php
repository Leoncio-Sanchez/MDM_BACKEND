<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\ValueObjects;

use App\Modules\Licencias\Domain\Exceptions\LicenciaInvalida;

final readonly class NombreComercial
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw new LicenciaInvalida('El nombre comercial no puede estar vacío');
        }
        if (strlen($value) > 255) {
            throw new LicenciaInvalida('El nombre comercial excede 255 caracteres');
        }
        return new self($value);
    }
}
