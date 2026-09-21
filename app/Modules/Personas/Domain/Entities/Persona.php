<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\Entities;

use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Carbon\CarbonImmutable;

final readonly class Persona
{
    /** @param PersonaDocumento[] $documentos */
    public function __construct(
        public ?int $id,
        public TipoPersona $tipoPersona,
        public Sexo $sexo,
        public ?string $apellidoPaterno,
        public ?string $apellidoMaterno,
        public string $nombres,
        public string $direccion,
        public string $distrito,
        public string $provincia,
        public string $departamento,
        public ?string $telefono,
        public ?string $correoElectronico,
        public CarbonImmutable $fechaCreacion,
        public CarbonImmutable $fechaActualizacion,
        public int $userId,
        public array $documentos = [],
    ) {}

    public static function crear(
        TipoPersona $tipoPersona,
        Sexo $sexo,
        string $nombres,
        string $direccion,
        string $distrito,
        string $provincia,
        string $departamento,
        int $userId,
        ?string $apellidoPaterno = null,
        ?string $apellidoMaterno = null,
        ?string $telefono = null,
        ?string $correoElectronico = null,
    ): self {
        $now = CarbonImmutable::now();

        return new self(
            id: null,
            tipoPersona: $tipoPersona,
            sexo: $sexo,
            apellidoPaterno: $apellidoPaterno,
            apellidoMaterno: $apellidoMaterno,
            nombres: trim($nombres),
            direccion: trim($direccion),
            distrito: trim($distrito),
            provincia: trim($provincia),
            departamento: trim($departamento),
            telefono: $telefono,
            correoElectronico: $correoElectronico,
            fechaCreacion: $now,
            fechaActualizacion: $now,
            userId: $userId,
        );
    }

    public function actualizar(
        ?TipoPersona $tipoPersona = null,
        ?Sexo $sexo = null,
        ?string $nombres = null,
        ?string $direccion = null,
        ?string $distrito = null,
        ?string $provincia = null,
        ?string $departamento = null,
        ?string $apellidoPaterno = null,
        ?string $apellidoMaterno = null,
        ?string $telefono = null,
        ?string $correoElectronico = null,
    ): self {
        return new self(
            id: $this->id,
            tipoPersona: $tipoPersona ?? $this->tipoPersona,
            sexo: $sexo ?? $this->sexo,
            apellidoPaterno: $apellidoPaterno ?? $this->apellidoPaterno,
            apellidoMaterno: $apellidoMaterno ?? $this->apellidoMaterno,
            nombres: $nombres !== null ? trim($nombres) : $this->nombres,
            direccion: $direccion ?? $this->direccion,
            distrito: $distrito ?? $this->distrito,
            provincia: $provincia ?? $this->provincia,
            departamento: $departamento ?? $this->departamento,
            telefono: $telefono ?? $this->telefono,
            correoElectronico: $correoElectronico ?? $this->correoElectronico,
            fechaCreacion: $this->fechaCreacion,
            fechaActualizacion: CarbonImmutable::now(),
            userId: $this->userId,
            documentos: $this->documentos,
        );
    }
}
