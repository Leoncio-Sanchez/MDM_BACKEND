<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;

final readonly class CrearPersonaDTO
{
    /** @param DocumentoDTO[] $documentos */
    public function __construct(
        public TipoPersona $tipoPersona,
        public Sexo $sexo,
        public string $nombres,
        public string $direccion,
        public string $distrito,
        public string $provincia,
        public string $departamento,
        public int $userId,
        public ?string $apellidoPaterno,
        public ?string $apellidoMaterno,
        public ?string $telefono,
        public ?string $correoElectronico,
        public array $documentos = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoPersona: TipoPersona::from($data['tipo_persona']),
            sexo: Sexo::from($data['sexo'] ?? 'X'),
            nombres: (string) $data['nombres'],
            direccion: (string) $data['direccion'],
            distrito: (string) $data['distrito'],
            provincia: (string) $data['provincia'],
            departamento: (string) $data['departamento'],
            userId: (int) $data['user_id'],
            apellidoPaterno: $data['apellido_paterno'] ?? null,
            apellidoMaterno: $data['apellido_materno'] ?? null,
            telefono: $data['telefono'] ?? null,
            correoElectronico: $data['correo_electronico'] ?? null,
            documentos: array_map(
                fn ($d) => DocumentoDTO::fromArray($d),
                $data['documentos'] ?? [],
            ),
        );
    }
}
