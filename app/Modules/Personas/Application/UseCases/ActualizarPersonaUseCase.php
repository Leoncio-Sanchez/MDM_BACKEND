<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Application\DTOs\ActualizarPersonaDTO;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use App\Modules\Personas\Domain\Exceptions\PersonaNoEncontrada;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
        private PersonaDocumentoRepositoryInterface $documentos,
    ) {}

    public function execute(int $id, ActualizarPersonaDTO $dto): Persona
    {
        return DB::transaction(function () use ($id, $dto): Persona {
            $persona = $this->personas->buscarPorId($id);
            if (!$persona) {
                throw PersonaNoEncontrada::porId($id);
            }

            $actualizada = $persona->actualizar(
                tipoPersona: $dto->tipoPersona,
                sexo: $dto->sexo,
                nombres: $dto->nombres,
                direccion: $dto->direccion,
                distrito: $dto->distrito,
                provincia: $dto->provincia,
                departamento: $dto->departamento,
                apellidoPaterno: $dto->apellidoPaterno,
                apellidoMaterno: $dto->apellidoMaterno,
                telefono: $dto->telefono,
                correoElectronico: $dto->correoElectronico,
            );

            $this->personas->actualizar($actualizada);

            if ($dto->documentos !== null) {
                $this->documentos->eliminarPorPersona($id);
                foreach ($dto->documentos as $docDto) {
                    $this->documentos->crear(
                        PersonaDocumento::crear(
                            tipoDocumentoIdentidadId: $docDto->tipoDocumentoIdentidadId,
                            numeroDocumento: $docDto->numeroDocumento,
                            personaId: $id,
                        )
                    );
                }
            }

            $ret = $this->personas->buscarPorId($id);
            if (!$ret) throw PersonaNoEncontrada::porId($id);
            return $ret;
        });
    }
}
