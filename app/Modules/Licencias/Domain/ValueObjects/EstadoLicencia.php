<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Domain\ValueObjects;

final readonly class EstadoLicencia
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function registrada(): self
    {
        return new self('REGISTRADA');
    }

    public static function inactiva(): self
    {
        return new self('INACTIVA');
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
