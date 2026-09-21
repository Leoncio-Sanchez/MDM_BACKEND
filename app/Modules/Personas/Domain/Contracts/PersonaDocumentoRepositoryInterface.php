<?php

declare(strict_types=1);

namespace App\Modules\Personas\Domain\Contracts;

use App\Modules\Personas\Domain\Entities\PersonaDocumento;

interface PersonaDocumentoRepositoryInterface
{
    /** @return PersonaDocumento[] */
    public function listarPorPersona(int $personaId): array;

    public function crear(PersonaDocumento $documento): PersonaDocumento;

    public function eliminarPorPersona(int $personaId): void;
}
