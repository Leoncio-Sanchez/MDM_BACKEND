<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

final readonly class PersonaReniecDTO
{
    public function __construct(
        public string $dni,
        public string $nombres,
        public string $apellidoPaterno,
        public string $apellidoMaterno,
        public ?string $direccion,
        public ?string $fechaNacimiento,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            dni: $data['nuDni'] ?? '',
            nombres: $data['prenombres'] ?? '',
            apellidoPaterno: $data['apePaterno'] ?? '',
            apellidoMaterno: $data['apeMaterno'] ?? '',
            direccion: $data['desDireccion'] ?? null,
            fechaNacimiento: $data['feNacimiento'] ?? null,
        );
    }
}
