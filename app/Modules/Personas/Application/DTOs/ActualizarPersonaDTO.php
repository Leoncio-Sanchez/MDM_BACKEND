<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use App\Modules\Personas\Domain\ValueObjects\Sexo;

final readonly class ActualizarPersonaDTO
{
    /** @param DocumentoDTO[]|null $documentos */
    public function __construct(
        public ?TipoPersona $tipoPersona,
        public ?Sexo $sexo,
        public ?string $nombres,
        public ?string $direccion,
        public ?string $distrito,
        public ?string $provincia,
        public ?string $departamento,
        public ?string $apellidoPaterno,
        public ?string $apellidoMaterno,
        public ?string $telefono,
        public ?string $correoElectronico,
        public ?array $documentos,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoPersona: isset($data['tipo_persona']) ? TipoPersona::from($data['tipo_persona']) : null,
            sexo: isset($data['sexo']) ? Sexo::from($data['sexo']) : null,
            nombres: $data['nombres'] ?? null,
            direccion: $data['direccion'] ?? null,
            distrito: $data['distrito'] ?? null,
            provincia: $data['provincia'] ?? null,
            departamento: $data['departamento'] ?? null,
            apellidoPaterno: array_key_exists('apellido_paterno', $data) ? $data['apellido_paterno'] : null,
            apellidoMaterno: array_key_exists('apellido_materno', $data) ? $data['apellido_materno'] : null,
            telefono: array_key_exists('telefono', $data) ? $data['telefono'] : null,
            correoElectronico: array_key_exists('correo_electronico', $data) ? $data['correo_electronico'] : null,
            documentos: isset($data['documentos']) ? array_map(
                fn ($d) => DocumentoDTO::fromArray($d),
                $data['documentos']
            ) : null,
        );
    }
}
