<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\ValueObjects;
use App\Modules\Licencias\Domain\Exceptions\NombreComercialInvalido;

final readonly class NombreComercial
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if ($value === '') {
            throw new NombreComercialInvalido('El nombre comercial no puede estar vacío.');
        }
        if (mb_strlen($value) > 250) {
            throw new NombreComercialInvalido('El nombre comercial no puede exceder los 250 caracteres.');
        }
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
