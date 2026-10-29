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
 * - inspector:   Inspector de campo (solo sus locales asignados)
 * - jefe_itse:   Jefatura de ITSE: asigna inspectores y administra sus cuentas (requiere "itse")
 * - reportes:    Solo lectura y descarga de todos los reportes (Subgerente, Auditor)
 */
final readonly class Permisos
{
    public const EXPEDIENTES = 'expedientes';

    public const LICENCIAS = 'licencias';

    public const ITSE = 'itse';

    public const ADMIN = 'admin';

    public const INSPECTOR = 'inspector';

    public const JEFE_ITSE = 'jefe_itse';

    public const REPORTES = 'reportes';

    public const TODOS = [
        self::EXPEDIENTES, self::LICENCIAS, self::ITSE, self::ADMIN,
        self::INSPECTOR, self::JEFE_ITSE, self::REPORTES,
    ];

    public function __construct(
        public bool $expedientes = false,
        public bool $licencias = false,
        public bool $itse = false,
        public bool $admin = false,
        public bool $inspector = false,
        public bool $jefeItse = false,
        public bool $reportes = false,
    ) {}

    /**
     * El superusuario tiene todos los accesos aunque su perfil no los marque.
     * "inspector" no es un privilegio sino una función (a quién se le asignan locales): no se le da.
     */
    public static function todos(): self
    {
        return new self(true, true, true, true, inspector: false, jefeItse: true, reportes: true);
    }

    public function tiene(string $permiso): bool
    {
        return match ($permiso) {
            self::EXPEDIENTES => $this->expedientes,
            self::LICENCIAS => $this->licencias,
            self::ITSE => $this->itse,
            self::ADMIN => $this->admin,
            self::INSPECTOR => $this->inspector,
            self::JEFE_ITSE => $this->jefeItse,
            self::REPORTES => $this->reportes,
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

    /** @return array{expedientes: bool, licencias: bool, itse: bool, admin: bool, inspector: bool, jefe_itse: bool, reportes: bool} */
    public function toArray(): array
    {
        return [
            'expedientes' => $this->expedientes,
            'licencias' => $this->licencias,
            'itse' => $this->itse,
            'admin' => $this->admin,
            'inspector' => $this->inspector,
            'jefe_itse' => $this->jefeItse,
            'reportes' => $this->reportes,
        ];
    }
}
