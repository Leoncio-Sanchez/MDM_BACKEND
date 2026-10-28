<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\ValueObjects;

/** Lo que el formulario de Personas necesita de SUNAT para una persona jurídica. */
final readonly class ContribuyenteSunat
{
    public function __construct(
        public string $ruc,
        public string $razonSocial,
        // Estado y condición del contribuyente, p. ej. "ACTIVO" / "HABIDO"
        public string $estado,
        public string $condicion,
        public string $direccion,
        public string $departamento,
        public string $provincia,
        public string $distrito,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'ruc' => $this->ruc,
            'razon_social' => $this->razonSocial,
            'estado' => $this->estado,
            'condicion' => $this->condicion,
            'direccion' => $this->direccion,
            'departamento' => $this->departamento,
            'provincia' => $this->provincia,
            'distrito' => $this->distrito,
        ];
    }
}
