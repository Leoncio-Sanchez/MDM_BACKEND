import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Personas/Infrastructure'
dirs = ['Persistence/Models', 'Persistence/Repositories', 'External', 'Providers']
for d in dirs:
    os.makedirs(f"{base}/{d}", exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

write('Persistence/Models/PersonaModel.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\Persistence\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class PersonaModel extends Model
{
    protected $table = 'personas';
    public $timestamps = false;

    protected $fillable = [
        'tipo_persona',
        'sexo',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'direccion',
        'distrito',
        'provincia',
        'departamento',
        'telefono',
        'correo_electronico',
        'fecha_creacion',
        'fecha_actualizacion',
        'user_id',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function documentos(): HasMany
    {
        return $this->hasMany(PersonaDocumentoModel::class, 'persona_id', 'id');
    }
}
""")

write('Persistence/Models/PersonaDocumentoModel.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\Persistence\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class PersonaDocumentoModel extends Model
{
    protected $table = 'personas_documentos';
    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'tipo_documento_identidad_id',
        'numero_documento',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id', 'id');
    }
}
""")

write('Persistence/Repositories/EloquentPersonaRepository.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\Persistence\\Repositories;

use App\\Modules\\Personas\\Application\\Mappers\\PersonaMapper;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\Persona;
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Models\\PersonaModel;
use Illuminate\\Support\\Facades\\DB;

final class EloquentPersonaRepository implements PersonaRepositoryInterface
{
    public function __construct(
        private readonly PersonaMapper $mapper,
    ) {}

    /** @return array{items: Persona[], total: int} */
    public function paginar(int $pagina, int $porPagina): array
    {
        $query = PersonaModel::with('documentos')->orderBy('id');
        
        $total = $query->count();
        $models = $query->skip(($pagina - 1) * $porPagina)->take($porPagina)->get();

        return [
            'items' => $models->map(fn (PersonaModel $m) => $this->mapper->toDomain($m))->all(),
            'total' => $total,
        ];
    }

    public function buscarPorId(int $id): ?Persona
    {
        $model = PersonaModel::with('documentos')->find($id);
        if (!$model) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    /** @return array<int, array<string, mixed>> */
    public function buscarPorFiltro(string $filtro, string $valor): array
    {
        $filtro = strtoupper(trim($filtro));
        
        $sqlNombre = "
            SELECT p.id AS persona_id
            FROM personas p
            WHERE TRIM(
                COALESCE(p.apellido_paterno, '') || ' ' ||
                COALESCE(p.apellido_materno, '') || ' ' ||
                COALESCE(p.nombres, '')
            ) ILIKE ?
        ";

        $sqlDocumento = "
            SELECT DISTINCT pd.persona_id
            FROM personas_documentos pd
            WHERE pd.numero_documento = ?
        ";

        $sqlId = "
            SELECT ?::INTEGER AS persona_id
        ";

        $subquery = match ($filtro) {
            'NOMBRE' => $sqlNombre,
            'DOCUMENTO' => $sqlDocumento,
            'ID' => $sqlId,
            default => throw new \\InvalidArgumentException("Filtro no válido."),
        };

        $valorParam = match ($filtro) {
            'NOMBRE' => '%' . str_replace(' ', '%', $valor) . '%',
            default => $valor,
        };

        $sql = "
        WITH personas_filtradas AS (
            {$subquery}
        ),
        documentos_concatenados AS (
            SELECT
                pf.persona_id,
                STRING_AGG(
                    tdi.nombre || ' ' || pd.numero_documento,
                    ', '
                    ORDER BY tdi.nombre || ' ' || pd.numero_documento
                ) AS documento_concatenado
            FROM personas_filtradas pf
            LEFT JOIN personas_documentos pd
                   ON pf.persona_id = pd.persona_id
            LEFT JOIN tipos_documento_identidad tdi
                   ON pd.tipo_documento_identidad_id = tdi.id
            GROUP BY pf.persona_id
        )
        SELECT
            p.id,
            p.tipo_persona,
            p.apellido_paterno,
            p.apellido_materno,
            p.nombres,
            TRIM(
                COALESCE(p.apellido_paterno, '') || ' ' ||
                COALESCE(p.apellido_materno, '') || ' ' ||
                COALESCE(p.nombres, '')
            )                       AS persona_nombre,
            p.direccion,
            p.distrito,
            p.provincia,
            p.departamento,
            p.telefono,
            p.correo_electronico,
            p.fecha_creacion,
            p.fecha_actualizacion,
            p.user_id,
            dc.documento_concatenado
        FROM documentos_concatenados dc
        INNER JOIN personas p ON dc.persona_id = p.id
        ORDER BY TRIM(
            COALESCE(p.apellido_paterno, '') || ' ' ||
            COALESCE(p.apellido_materno, '') || ' ' ||
            COALESCE(p.nombres, '')
        )
        ";

        $results = DB::select($sql, [$valorParam]);

        return array_map(fn ($row) => (array) $row, $results);
    }

    public function crear(Persona $persona): Persona
    {
        $model = PersonaModel::create($this->mapper->toPersistence($persona));
        $model->load('documentos');
        return $this->mapper->toDomain($model);
    }

    public function actualizar(Persona $persona): Persona
    {
        /** @var PersonaModel $model */
        $model = PersonaModel::findOrFail($persona->id);
        $model->update($this->mapper->toPersistence($persona));
        $model->load('documentos');
        return $this->mapper->toDomain($model);
    }

    public function eliminar(int $id): void
    {
        PersonaModel::destroy($id);
    }

    public function tieneDependientes(int $id): bool
    {
        // Django model sets on_delete=PROTECT for Expediente (solicitante_id, representante_id)
        // Licencia (titular_id, conductor_id), ITSE (titular_id, conductor_id).
        
        $hasExpedientes = DB::table('expedientes')
            ->where('solicitante_id', $id)
            ->orWhere('representante_id', $id)
            ->exists();
            
        $hasLicencias = DB::table('licencias_funcionamiento')
            ->where('titular_id', $id)
            ->orWhere('conductor_id', $id)
            ->exists();
            
        $hasItse = DB::table('itse')
            ->where('titular_id', $id)
            ->orWhere('conductor_id', $id)
            ->exists();

        return $hasExpedientes || $hasLicencias || $hasItse;
    }
}
""")

write('Persistence/Repositories/EloquentPersonaDocumentoRepository.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\Persistence\\Repositories;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento;
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Models\\PersonaDocumentoModel;

final class EloquentPersonaDocumentoRepository implements PersonaDocumentoRepositoryInterface
{
    /** @return PersonaDocumento[] */
    public function listarPorPersona(int $personaId): array
    {
        $models = PersonaDocumentoModel::where('persona_id', $personaId)->get();

        return $models->map(fn (PersonaDocumentoModel $m) => new PersonaDocumento(
            id: $m->id,
            personaId: $m->persona_id,
            tipoDocumentoIdentidadId: $m->tipo_documento_identidad_id,
            numeroDocumento: $m->numero_documento,
        ))->all();
    }

    public function crear(PersonaDocumento $documento): PersonaDocumento
    {
        $model = PersonaDocumentoModel::create([
            'persona_id' => $documento->personaId,
            'tipo_documento_identidad_id' => $documento->tipoDocumentoIdentidadId,
            'numero_documento' => $documento->numeroDocumento,
        ]);

        return new PersonaDocumento(
            id: $model->id,
            personaId: $model->persona_id,
            tipoDocumentoIdentidadId: $model->tipo_documento_identidad_id,
            numeroDocumento: $model->numero_documento,
        );
    }

    public function eliminarPorPersona(int $personaId): void
    {
        PersonaDocumentoModel::where('persona_id', $personaId)->delete();
    }
}
""")

write('External/ReniecHttpClient.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\External;

use App\\Modules\\Personas\\Application\\DTOs\\PersonaReniecDTO;
use App\\Modules\\Personas\\Domain\\Contracts\\ReniecClientInterface;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecNoDisponible;
use App\\Modules\\Personas\\Domain\\Exceptions\\ReniecRespuestaInvalida;
use Illuminate\\Support\\Facades\\Http;

final class ReniecHttpClient implements ReniecClientInterface
{
    public function __construct(
        private readonly string $url,
        private readonly string $dniUsuario,
        private readonly string $rucUsuario,
        private readonly string $password,
        private readonly int $timeout,
    ) {}

    public function consultarPorDni(string $dni): PersonaReniecDTO
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->url, [
                'nuDniConsulta' => $dni,
                'nuDniUsuario' => $this->dniUsuario,
                'nuRucUsuario' => $this->rucUsuario,
                'password' => $this->password,
                'out' => 'json',
            ]);
        } catch (\\Exception $e) {
            throw ReniecNoDisponible::porDni($dni);
        }

        if (!$response->successful()) {
            throw ReniecNoDisponible::porDni($dni);
        }

        $data = $response->json();

        if (!is_array($data) || !isset($data['consultarResponse'])) {
            throw ReniecRespuestaInvalida::porDni($dni);
        }

        $personaData = $data['consultarResponse']['return'] ?? $data['consultarResponse'];

        return PersonaReniecDTO::fromArray($personaData);
    }
}
""")

write('Providers/PersonasServiceProvider.php', """<?php
declare(strict_types=1);

namespace App\\Modules\\Personas\\Infrastructure\\Providers;

use App\\Modules\\Personas\\Domain\\Contracts\\PersonaDocumentoRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\PersonaRepositoryInterface;
use App\\Modules\\Personas\\Domain\\Contracts\\ReniecClientInterface;
use App\\Modules\\Personas\\Infrastructure\\External\\ReniecHttpClient;
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Repositories\\EloquentPersonaDocumentoRepository;
use App\\Modules\\Personas\\Infrastructure\\Persistence\\Repositories\\EloquentPersonaRepository;
use Illuminate\\Support\\ServiceProvider;

class PersonasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PersonaRepositoryInterface::class, EloquentPersonaRepository::class);
        $this->app->bind(PersonaDocumentoRepositoryInterface::class, EloquentPersonaDocumentoRepository::class);

        $this->app->singleton(ReniecClientInterface::class, function ($app) {
            return new ReniecHttpClient(
                url: config('services.reniec.url', 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar'),
                dniUsuario: config('services.reniec.dni_usuario', ''),
                rucUsuario: config('services.reniec.ruc_usuario', ''),
                password: config('services.reniec.password', ''),
                timeout: (int) config('services.reniec.timeout', 10),
            );
        });
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/../../Presentation/Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
        }
    }
}
""")

