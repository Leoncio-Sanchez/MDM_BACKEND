import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Personas'
dirs = [
    'Application/UseCases', 'Application/DTOs', 'Application/Mappers',
    'Domain/Exceptions'
]
for d in dirs:
    os.makedirs(f"{base}/{d}", exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

# Exceptions updates
write('Domain/Exceptions/PersonaTieneDependientes.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class PersonaTieneDependientes extends \\DomainException
{
    public static function porId(int $id): self
    {
        return new self("La persona con ID {$id} tiene dependientes y no puede ser eliminada.");
    }
}
""")

# Contract update
with open(f"{base}/Domain/Contracts/PersonaRepositoryInterface.php", "r") as f:
    repo_content = f.read()

if "tieneDependientes" not in repo_content:
    repo_content = repo_content.replace(
        "public function buscarPorFiltro(string $filtro, string $valor): array;",
        "public function buscarPorFiltro(string $filtro, string $valor): array;\n    public function tieneDependientes(int $id): bool;"
    )
    write('Domain/Contracts/PersonaRepositoryInterface.php', repo_content)


write('Application/DTOs/DocumentoDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

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
""")

write('Application/DTOs/CrearPersonaDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

use App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;

final readonly class CrearPersonaDTO
{
    /** @param DocumentoDTO[] $documentos */
    public function __construct(
        public TipoPersona $tipoPersona,
        public Sexo $sexo,
        public string $nombres,
        public string $direccion,
        public string $distrito,
        public string $provincia,
        public string $departamento,
        public int $userId,
        public ?string $apellidoPaterno,
        public ?string $apellidoMaterno,
        public ?string $telefono,
        public ?string $correoElectronico,
        public array $documentos = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoPersona: TipoPersona::from($data['tipo_persona']),
            sexo: Sexo::from($data['sexo'] ?? 'X'),
            nombres: (string) $data['nombres'],
            direccion: (string) $data['direccion'],
            distrito: (string) $data['distrito'],
            provincia: (string) $data['provincia'],
            departamento: (string) $data['departamento'],
            userId: (int) $data['user_id'],
            apellidoPaterno: $data['apellido_paterno'] ?? null,
            apellidoMaterno: $data['apellido_materno'] ?? null,
            telefono: $data['telefono'] ?? null,
            correoElectronico: $data['correo_electronico'] ?? null,
            documentos: array_map(
                fn ($d) => DocumentoDTO::fromArray($d),
                $data['documentos'] ?? [],
            ),
        );
    }
}
""")

write('Application/DTOs/ActualizarPersonaDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

use App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona;
use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;

final readonly class ActualizarPersonaDTO
{
    /** @param DocumentoDTO[]|null $documentos */
    public function __construct(
        public ?TipoPersona $tipoPersona,
        public ?Sexo $sexo,
        public ?string $nombres,
        public ?string $direccion,
        public ?string $distrito,
        public ?string $provincia,
        public ?string $departamento,
        public ?string $apellidoPaterno,
        public ?string $apellidoMaterno,
        public ?string $telefono,
        public ?string $correoElectronico,
        public ?array $documentos,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tipoPersona: isset($data['tipo_persona']) ? TipoPersona::from($data['tipo_persona']) : null,
            sexo: isset($data['sexo']) ? Sexo::from($data['sexo']) : null,
            nombres: $data['nombres'] ?? null,
            direccion: $data['direccion'] ?? null,
            distrito: $data['distrito'] ?? null,
            provincia: $data['provincia'] ?? null,
            departamento: $data['departamento'] ?? null,
            apellidoPaterno: array_key_exists('apellido_paterno', $data) ? $data['apellido_paterno'] : null,
            apellidoMaterno: array_key_exists('apellido_materno', $data) ? $data['apellido_materno'] : null,
            telefono: array_key_exists('telefono', $data) ? $data['telefono'] : null,
            correoElectronico: array_key_exists('correo_electronico', $data) ? $data['correo_electronico'] : null,
            documentos: isset($data['documentos']) ? array_map(
                fn ($d) => DocumentoDTO::fromArray($d),
                $data['documentos']
            ) : null,
        );
    }
}
""")

write('Application/DTOs/BuscarPersonasDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

final readonly class BuscarPersonasDTO
{
    public function __construct(
        public string $filtro,
        public string $valor,
    ) {}
}
""")

write('Application/DTOs/ConsultarReniecDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

final readonly class ConsultarReniecDTO
{
    public function __construct(
        public string $dni,
    ) {}
}
""")

write('Application/UseCases/ListarPersonasUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;

final readonly class ListarPersonasUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    /** @return array{items: \\App\\Modules\\Personas\\Domain\\Entities\\Persona[], total: int} */
    public function execute(int $pagina = 1, int $porPagina = 10): array
    {
        return $this->personas->paginar($pagina, $porPagina);
    }
}
""")

write('Application/UseCases/CrearPersonaUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Application\\DTOs\\CrearPersonaDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento;
use Illuminate\\Support\\Facades\\DB;

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

            return $this->personas->buscarPorId((int)$persistida->id);
        });
    }
}
""")

write('Application/UseCases/ObtenerPersonaUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;

final readonly class ObtenerPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    public function execute(int $id): Persona
    {
        $persona = $this->personas->buscarPorId($id);
        if (!$persona) {
            throw PersonaNoEncontrada::porId($id);
        }
        return $persona;
    }
}
""")

write('Application/UseCases/ActualizarPersonaUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Application\\DTOs\\ActualizarPersonaDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;
use Illuminate\\Support\\Facades\\DB;

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

            return $this->personas->buscarPorId($id);
        });
    }
}
""")

write('Application/UseCases/EliminarPersonaUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaTieneDependientes;
use Illuminate\\Support\\Facades\\DB;

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
""")

write('Application/UseCases/BuscarPersonasUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Application\\DTOs\\BuscarPersonasDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;

final readonly class BuscarPersonasUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(BuscarPersonasDTO $dto): array
    {
        return $this->personas->buscarPorFiltro($dto->filtro, $dto->valor);
    }
}
""")

write('Application/UseCases/ListarSexosUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;

final readonly class ListarSexosUseCase
{
    /** @return array<int, array{value: string, label: string}> */
    public function execute(): array
    {
        return array_map(
            fn (Sexo $s) => ['value' => $s->value, 'label' => $s->name === 'PrefieroNoDecir' ? 'Prefiero no decirlo' : $s->name],
            Sexo::cases(),
        );
    }
}
""")

write('Application/UseCases/ListarDocumentosPersonaUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Exceptions\\PersonaNoEncontrada;

final readonly class ListarDocumentosPersonaUseCase
{
    public function __construct(
        private PersonaRepositoryInterface $personas,
        private PersonaDocumentoRepositoryInterface $documentos,
    ) {}

    /** @return \\App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento[] */
    public function execute(int $personaId): array
    {
        $persona = $this->personas->buscarPorId($personaId);
        if (!$persona) {
            throw PersonaNoEncontrada::porId($personaId);
        }

        return $this->documentos->listarPorPersona($personaId);
    }
}
""")

write('Application/UseCases/ConsultarReniecUseCase.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\UseCases;

use App\\Modules\\Personas\\Application\\DTOs\\ConsultarReniecDTO;
use App\\Modules\\Personas\\Application\\DTOs\\PersonaReniecDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\ReniecClientInterface;
use App\\Modules\\Personas\\Domain\\Exceptions\\DocumentoInvalido;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecNoDisponible;
use Psr\\Log\\LoggerInterface;

final readonly class ConsultarReniecUseCase
{
    public function __construct(
        private ReniecClientInterface $reniec,
        private LoggerInterface $logger,
    ) {}

    public function execute(ConsultarReniecDTO $dto): PersonaReniecDTO
    {
        if (!preg_match('/^\\d{8}$/', $dto->dni)) {
            throw new DocumentoInvalido("El DNI debe tener exactamente 8 dígitos numéricos.");
        }

        $this->logger->info('Consultando RENIEC', ['dni' => $dto->dni]);

        try {
            return $this->reniec->consultarPorDni($dto->dni);
        } catch (ReniecNoDisponible $e) {
            $this->logger->error('RENIEC no disponible', ['dni' => $dto->dni]);
            throw $e;
        }
    }
}
""")

