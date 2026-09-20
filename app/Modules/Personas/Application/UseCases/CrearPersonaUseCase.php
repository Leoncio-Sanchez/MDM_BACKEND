<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Application\DTOs\CrearPersonaDTO;
use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Entities\Persona;
use App\Modules\Personas\Domain\Entities\PersonaDocumento;
use Illuminate\Support\Facades\DB;

final readonly class CrearPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
        private PersonaDocumentoRepositoryInterface $documentos,
    ) {}

    public function execute(CrearPersonaDTO $dto): Persona
    {
        return DB::transaction(function () use ($dto): Persona {
            $persona = Persona::crear(
                tipoPersona: $dto->tipoPersona,
                sexo: $dto->sexo,
                nombres: $dto->nombres,
                direccion: $dto->direccion,
                distrito: $dto->distrito,
                provincia: $dto->provincia,
                departamento: $dto->departamento,
                userId: $dto->userId,
                apellidoPaterno: $dto->apellidoPaterno,
                apellidoMaterno: $dto->apellidoMaterno,
                telefono: $dto->telefono,
                correoElectronico: $dto->correoElectronico,
            );

            $persistida = $this->personas->crear($persona);

            foreach ($dto->documentos as $docDto) {
                $this->documentos->crear(
                    PersonaDocumento::crear(
                        tipoDocumentoIdentidadId: $docDto->tipoDocumentoIdentidadId,
                        numeroDocumento: $docDto->numeroDocumento,
                        personaId: $persistida->id,
                    )
                );
            }

            $ret = $this->personas->buscarPorId((int)$persistida->id);
            if (!$ret) throw new \RuntimeException('Persona no encontrada tras crear');
            return $ret;
        });
    }
}
