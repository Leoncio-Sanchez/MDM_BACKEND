<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\ValueObjects;

/**
 * Lo que el formulario de Personas necesita de RENIEC. A propósito NO incluye la foto,
 * el estado civil ni las restricciones que también devuelve PIDE (minimización de datos).
 */
final readonly class PersonaReniec
{
    public function __construct(
        public string $dni,
        public string $nombres,
        public string $apellidoPaterno,
        public string $apellidoMaterno,
        public string $direccion,
        public string $departamento,
        public string $provincia,
        public string $distrito,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellidoPaterno,
            'apellido_materno' => $this->apellidoMaterno,
            'direccion' => $this->direccion,
            'departamento' => $this->departamento,
            'provincia' => $this->provincia,
            'distrito' => $this->distrito,
        ];
    }
}
