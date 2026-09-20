<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;
use App\Modules\Personas\Domain\Exceptions\PersonaTieneDependientes;
use Illuminate\Support\Facades\DB;

final readonly class EliminarPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
        private PersonaDocumentoRepositoryInterface $documentos,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $persona = $this->personas->buscarPorId($id);
            if (!$persona) {
                throw PersonaNoEncontrada::porId($id);
            }

            if ($this->personas->tieneDependientes($id)) {
                throw PersonaTieneDependientes::porId($id);
            }

            $this->documentos->eliminarPorPersona($id);
            $this->personas->eliminar($id);
        });
    }
}
