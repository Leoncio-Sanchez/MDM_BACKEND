import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Personas'
dirs = [
    'Domain/Entities', 'Domain/Contracts', 'Domain/Exceptions', 'Domain/ValueObjects',
    'Application/DTOs'
]
for d in dirs:
    os.makedirs(f"{base}/{d}", exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

write('Domain/ValueObjects/TipoPersona.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\ValueObjects;

enum TipoPersona: string
{
    case Natural = 'N';
    case Juridica = 'J';

    public function label(): string
    {
        return match ($this) {
            self::Natural => 'Natural',
            self::Juridica => 'Jurídica',
        };
    }
}
""")

write('Domain/ValueObjects/Sexo.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\ValueObjects;

enum Sexo: string
{
    case Masculino = 'M';
    case Femenino = 'F';
    case PrefieroNoDecir = 'X';
}
""")

write('Domain/ValueObjects/NumeroDocumento.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\ValueObjects;

use App\\Modules\\Personas\\Domain\\Exceptions\\DocumentoInvalido;

final readonly class NumeroDocumento
{
    private function __construct(public string $valor) {}

    public static function crear(string $valor): self
    {
        $valor = trim($valor);
        if ($valor === '') {
            throw DocumentoInvalido::vacio();
        }
        return new self($valor);
    }
}
""")

write('Domain/ValueObjects/DocumentoConcatenado.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\ValueObjects;

final readonly class DocumentoConcatenado
{
    private function __construct(public string $valor) {}

    public static function crear(?string $valor): self
    {
        return new self($valor !== null ? trim($valor) : '');
    }
}
""")

write('Domain/Exceptions/PersonaNoEncontrada.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class PersonaNoEncontrada extends \\DomainException
{
    public static function porId(int $id): self
    {
        return new self("Persona con ID {$id} no encontrada.");
    }
}
""")

write('Domain/Exceptions/DocumentoInvalido.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class DocumentoInvalido extends \\DomainException
{
    public static function vacio(): self
    {
        return new self("El número de documento no puede estar vacío.");
    }
}
""")

write('Domain/Exceptions/ReniecNoDisponible.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class ReniecNoDisponible extends \\DomainException
{
    public static function porDni(string $dni): self
    {
        return new self("El servicio RENIEC no está disponible para el DNI {$dni}.");
    }
}
""")

write('Domain/Exceptions/ReniecRespuestaInvalida.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class ReniecRespuestaInvalida extends \\DomainException
{
    public static function porDni(string $dni): self
    {
        return new self("Respuesta inválida de RENIEC para el DNI {$dni}.");
    }
}
""")

write('Domain/Exceptions/TipoDocumentoNoValido.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Exceptions;

class TipoDocumentoNoValido extends \\DomainException
{
    public static function crear(): self
    {
        return new self("El tipo de documento de identidad no es válido.");
    }
}
""")

write('Domain/Contracts/PersonaRepositoryInterface.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Contracts;

use App\\Modules\\Personas\\Domain\\Entities\\Persona;

interface PersonaRepositoryInterface
{
    /** @return array{items: Persona[], total: int} */
    public function paginar(int $pagina, int $porPagina): array;

    public function buscarPorId(int $id): ?Persona;

    /** @return array<int, array<string, mixed>> */
    public function buscarPorFiltro(string $filtro, string $valor): array;

    public function crear(Persona $persona): Persona;

    public function actualizar(Persona $persona): Persona;

    public function eliminar(int $id): void;
}
""")

write('Domain/Contracts/PersonaDocumentoRepositoryInterface.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Contracts;

use App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento;

interface PersonaDocumentoRepositoryInterface
{
    /** @return PersonaDocumento[] */
    public function listarPorPersona(int $personaId): array;

    public function crear(PersonaDocumento $documento): PersonaDocumento;

    public function eliminarPorPersona(int $personaId): void;
}
""")

write('Domain/Contracts/ReniecClientInterface.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Contracts;

use App\\Modules\\Personas\\Application\\DTOs\\PersonaReniecDTO;

interface ReniecClientInterface
{
    public function consultarPorDni(string $dni): PersonaReniecDTO;
}
""")

write('Application/DTOs/PersonaReniecDTO.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Application\\DTOs;

final readonly class PersonaReniecDTO
{
    public function __construct(
        public string $dni,
        public string $nombres,
        public string $apellidoPaterno,
        public string $apellidoMaterno,
        public ?string $direccion,
        public ?string $fechaNacimiento,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            dni: $data['nuDni'] ?? '',
            nombres: $data['prenombres'] ?? '',
            apellidoPaterno: $data['apePaterno'] ?? '',
            apellidoMaterno: $data['apeMaterno'] ?? '',
            direccion: $data['desDireccion'] ?? null,
            fechaNacimiento: $data['feNacimiento'] ?? null,
        );
    }
}
""")

write('Domain/Entities/Persona.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Entities;

use App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo;
use App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona;
use Carbon\\CarbonImmutable;

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
""")

write('Domain/Entities/PersonaDocumento.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Domain\\Entities;

use App\\Modules\\Personas\\Domain\\Exceptions\\DocumentoInvalido;

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
""")

