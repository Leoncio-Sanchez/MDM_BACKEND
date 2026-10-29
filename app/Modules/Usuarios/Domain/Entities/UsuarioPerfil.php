<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain\Entities;

use App\Modules\Usuarios\Domain\Exceptions\PerfilInvalido;

/** Casillas de acceso del perfil (usuarios_perfiles), con las mismas claves que las columnas. */
final readonly class UsuarioPerfil
{
    public const CASILLAS = [
        'expedientes', 'licencias', 'itse', 'admin',
        'es_inspector', 'es_jefe_itse', 'reportes_completos',
    ];

    public function __construct(
        public bool $expedientes = false,
        public bool $licencias = false,
        public bool $itse = false,
        public bool $admin = false,
        public bool $esInspector = false,
        public bool $esJefeItse = false,
        public bool $reportesCompletos = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            expedientes: (bool) ($data['expedientes'] ?? false),
            licencias: (bool) ($data['licencias'] ?? false),
            itse: (bool) ($data['itse'] ?? false),
            admin: (bool) ($data['admin'] ?? false),
            esInspector: (bool) ($data['es_inspector'] ?? false),
            esJefeItse: (bool) ($data['es_jefe_itse'] ?? false),
            reportesCompletos: (bool) ($data['reportes_completos'] ?? false),
        );
    }

    /** @throws PerfilInvalido */
    public function asegurarConsistencia(): void
    {
        // La jefatura de ITSE trabaja dentro del módulo ITSE (también lo exige la base)
        if ($this->esJefeItse && ! $this->itse) {
            throw PerfilInvalido::jefeSinItse();
        }
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'expedientes' => $this->expedientes,
            'licencias' => $this->licencias,
            'itse' => $this->itse,
            'admin' => $this->admin,
            'es_inspector' => $this->esInspector,
            'es_jefe_itse' => $this->esJefeItse,
            'reportes_completos' => $this->reportesCompletos,
        ];
    }
}
