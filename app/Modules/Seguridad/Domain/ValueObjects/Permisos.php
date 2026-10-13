<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Domain\ValueObjects;

/**
 * Accesos del perfil operativo (tabla usuarios_perfiles).
 *
 * - expedientes: Expedientes TUPA
 * - licencias:   Licencias de funcionamiento
 * - itse:        Inspecciones ITSE e inspectores
 * - admin:       Catálogos maestros y gestión de usuarios
 */
final readonly class Permisos
{
    public const EXPEDIENTES = 'expedientes';
    public const LICENCIAS = 'licencias';
    public const ITSE = 'itse';
    public const ADMIN = 'admin';

    public const TODOS = [self::EXPEDIENTES, self::LICENCIAS, self::ITSE, self::ADMIN];

    public function __construct(
        public bool $expedientes = false,
        public bool $licencias = false,
        public bool $itse = false,
        public bool $admin = false,
    ) {}

    /** El superusuario tiene todos los accesos aunque su perfil no los marque. */
    public static function todos(): self
    {
        return new self(true, true, true, true);
    }

    public function tiene(string $permiso): bool
    {
        return match ($permiso) {
            self::EXPEDIENTES => $this->expedientes,
            self::LICENCIAS => $this->licencias,
            self::ITSE => $this->itse,
            self::ADMIN => $this->admin,
            default => false,
        };
    }

    /** @param list<string> $permisos */
    public function tieneAlguno(array $permisos): bool
    {
        foreach ($permisos as $permiso) {
            if ($this->tiene($permiso)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{expedientes: bool, licencias: bool, itse: bool, admin: bool} */
    public function toArray(): array
    {
        return [
            'expedientes' => $this->expedientes,
            'licencias' => $this->licencias,
            'itse' => $this->itse,
            'admin' => $this->admin,
        ];
    }
}
