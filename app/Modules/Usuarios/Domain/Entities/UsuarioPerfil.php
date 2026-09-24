<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain\Entities;

final readonly class UsuarioPerfil
{
    public function __construct(
        public bool $expedientes = false,
        public bool $licencias = false,
        public bool $itse = false,
        public bool $admin = false,
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
        );
    }

    /**
     * @return array{expedientes: bool, licencias: bool, itse: bool, admin: bool}
     */
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
