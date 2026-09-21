<?php

declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;

final readonly class ListarDocumentosPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
        private PersonaDocumentoRepositoryInterface $documentos,
    ) {}

    /** @return PersonaDocumento[] */
    public function execute(int $personaId): array
    {
        $persona = $this->personas->buscarPorId($personaId);
        if (! $persona) {
            throw PersonaNoEncontrada::porId($personaId);
        }

        return $this->documentos->listarPorPersona($personaId);
    }
}
