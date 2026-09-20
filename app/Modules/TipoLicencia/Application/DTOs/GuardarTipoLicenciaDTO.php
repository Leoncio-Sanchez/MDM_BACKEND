<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Application\DTOs;

final readonly class GuardarTipoLicenciaDTO {
    public function __construct(
        public string $codigo,
        public string $nombre,
        public bool $estaActivo
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            codigo: (string) ($data['codigo'] ?? ''),
            nombre: (string) ($data['nombre'] ?? ''),
            estaActivo: (bool) ($data['esta_activo'] ?? true)
        );
    }
}
