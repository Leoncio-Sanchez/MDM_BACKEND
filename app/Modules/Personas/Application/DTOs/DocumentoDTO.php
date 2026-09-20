<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\DTOs;

final readonly class DocumentoDTO
{
    public function __construct(
        public int $tipoDocumentoIdentidadId,
        public string $numeroDocumento,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoDocumentoIdentidadId: (int) $data['tipo_documento_identidad_id'],
            numeroDocumento: (string) $data['numero_documento'],
        );
    }
}
