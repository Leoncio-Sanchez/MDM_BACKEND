<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Entities;

use App\Modules\Personas\Domain\Exceptions\DocumentoInvalido;

final readonly class PersonaDocumento
{
    public function __construct(
        public ?int $id,
        public ?int $personaId,
        public int $tipoDocumentoIdentidadId,
        public string $numeroDocumento,
    ) {}

    public static function crear(
        int $tipoDocumentoIdentidadId,
        string $numeroDocumento,
        ?int $personaId = null,
    ): self {
        $numero = trim($numeroDocumento);

        if ($numero === '') {
            throw DocumentoInvalido::vacio();
        }

        return new self(
            id: null,
            personaId: $personaId,
            tipoDocumentoIdentidadId: $tipoDocumentoIdentidadId,
            numeroDocumento: $numero,
        );
    }
}
