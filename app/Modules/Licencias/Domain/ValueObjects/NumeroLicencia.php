<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\ValueObjects;
use App\Modules\Licencias\Domain\Exceptions\NumeroLicenciaInvalido;

final readonly class NumeroLicencia
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value <= 0) {
            throw new NumeroLicenciaInvalido('El número de licencia debe ser un entero positivo.');
        }
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
