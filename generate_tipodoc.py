import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/TipoDocumentoIdentidad'
dirs = [
    'Domain/Entities', 'Domain/Contracts', 'Domain/Exceptions',
    'Application/UseCases', 'Application/DTOs',
    'Infrastructure/Persistence/Models', 'Infrastructure/Persistence/Repositories',
    'Infrastructure/Providers',
    'Presentation/Controllers', 'Presentation/Requests', 'Presentation/Resources', 'Presentation/Routes',
    'Tests/Feature'
]
for d in dirs:
    os.makedirs(f"{base}/{d}", exist_ok=True)

def write(path, content):
    with open(f"{base}/{path}", "w") as f:
        f.write(content)

# 1. Domain Entity
write('Domain/Entities/TipoDocumentoIdentidad.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities;

final readonly class TipoDocumentoIdentidad {
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public bool $esParaNatural,
        public bool $esParaJuridica
    ) {}

    public static function crear(string $codigo, string $nombre, bool $estaActivo, bool $esParaNatural, bool $esParaJuridica): self {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            estaActivo: $estaActivo,
            esParaNatural: $esParaNatural,
            esParaJuridica: $esParaJuridica
        );
    }

    public function actualizar(?string $codigo, ?string $nombre, ?bool $estaActivo, ?bool $esParaNatural, ?bool $esParaJuridica): self {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
            esParaNatural: $esParaNatural ?? $this->esParaNatural,
            esParaJuridica: $esParaJuridica ?? $this->esParaJuridica
        );
    }
}
""")

write('Domain/Exceptions/TipoDocumentoIdentidadNoEncontrado.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Domain\\Exceptions;

class TipoDocumentoIdentidadNoEncontrado extends \\DomainException {
    public function __construct() {
        parent::__construct('Tipo de documento de identidad no encontrado');
    }
}
""")

write('Domain/Contracts/TipoDocumentoIdentidadRepositoryInterface.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad;

interface TipoDocumentoIdentidadRepositoryInterface {
    /** @return TipoDocumentoIdentidad[] */
    public function obtenerTodos(string $tipoPersona): array;
    public function buscarPorId(int $id): ?TipoDocumentoIdentidad;
    public function guardar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad;
    public function actualizar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad;
    public function eliminar(int $id): void;
}
""")

write('Application/UseCases/ListarTiposDocumentoIdentidadUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;

final readonly class ListarTiposDocumentoIdentidadUseCase {
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}
    /** @return \\App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad[] */
    public function execute(string $tipoPersona): array {
        return $this->repository->obtenerTodos($tipoPersona);
    }
}
""")

write('Application/UseCases/ObtenerTipoDocumentoIdentidadUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Exceptions\\TipoDocumentoIdentidadNoEncontrado;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad;

final readonly class ObtenerTipoDocumentoIdentidadUseCase {
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}
    public function execute(int $id): TipoDocumentoIdentidad {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new TipoDocumentoIdentidadNoEncontrado();
        return $entity;
    }
}
""")

write('Application/DTOs/GuardarTipoDocumentoIdentidadDTO.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\DTOs;

final readonly class GuardarTipoDocumentoIdentidadDTO {
    public function __construct(
        public string $codigo,
        public string $nombre,
        public bool $estaActivo,
        public bool $esParaNatural,
        public bool $esParaJuridica
    ) {}
}
""")

write('Application/UseCases/CrearTipoDocumentoIdentidadUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\DTOs\\GuardarTipoDocumentoIdentidadDTO;

final readonly class CrearTipoDocumentoIdentidadUseCase {
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}
    public function execute(GuardarTipoDocumentoIdentidadDTO $dto): TipoDocumentoIdentidad {
        $entity = TipoDocumentoIdentidad::crear($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->esParaNatural, $dto->esParaJuridica);
        return $this->repository->guardar($entity);
    }
}
""")

write('Application/UseCases/ActualizarTipoDocumentoIdentidadUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Exceptions\\TipoDocumentoIdentidadNoEncontrado;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\DTOs\\GuardarTipoDocumentoIdentidadDTO;

final readonly class ActualizarTipoDocumentoIdentidadUseCase {
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}
    public function execute(int $id, GuardarTipoDocumentoIdentidadDTO $dto): TipoDocumentoIdentidad {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new TipoDocumentoIdentidadNoEncontrado();
        $updated = $entity->actualizar($dto->codigo, $dto->nombre, $dto->estaActivo, $dto->esParaNatural, $dto->esParaJuridica);
        return $this->repository->actualizar($updated);
    }
}
""")

write('Application/UseCases/EliminarTipoDocumentoIdentidadUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;

final readonly class EliminarTipoDocumentoIdentidadUseCase {
    public function __construct(private TipoDocumentoIdentidadRepositoryInterface $repository) {}
    public function execute(int $id): void {
        $this->repository->eliminar($id);
    }
}
""")

write('Infrastructure/Persistence/Models/TipoDocumentoIdentidadModel.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Persistence\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class TipoDocumentoIdentidadModel extends Model {
    protected $table = 'tipos_documento_identidad';
    public $timestamps = false;
    protected $fillable = ['codigo', 'nombre', 'esta_activo', 'es_para_natural', 'es_para_juridica'];
    protected $casts = [
        'esta_activo' => 'boolean',
        'es_para_natural' => 'boolean',
        'es_para_juridica' => 'boolean'
    ];
}
""")

write('Infrastructure/Persistence/Repositories/EloquentTipoDocumentoIdentidadRepository.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Persistence\\Repositories;

use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad;
use App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Persistence\\Models\\TipoDocumentoIdentidadModel;

class EloquentTipoDocumentoIdentidadRepository implements TipoDocumentoIdentidadRepositoryInterface {
    /** @return TipoDocumentoIdentidad[] */
    public function obtenerTodos(string $tipoPersona): array {
        $query = TipoDocumentoIdentidadModel::query();
        if ($tipoPersona === 'N') {
            $query->where('es_para_natural', true);
        } else {
            $query->where('es_para_juridica', true);
        }
        return $query->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }
    public function buscarPorId(int $id): ?TipoDocumentoIdentidad {
        $m = TipoDocumentoIdentidadModel::find($id);
        return $m ? $this->toDomain($m) : null;
    }
    public function guardar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'es_para_natural' => $entity->esParaNatural,
            'es_para_juridica' => $entity->esParaJuridica
        ]);
        return $this->toDomain($m);
    }
    public function actualizar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad {
        $m = TipoDocumentoIdentidadModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'es_para_natural' => $entity->esParaNatural,
            'es_para_juridica' => $entity->esParaJuridica
        ]);
        return $this->toDomain($m);
    }
    public function eliminar(int $id): void {
        TipoDocumentoIdentidadModel::destroy($id);
    }
    private function toDomain(TipoDocumentoIdentidadModel $m): TipoDocumentoIdentidad {
        return new TipoDocumentoIdentidad(
            $m->id, 
            $m->codigo, 
            $m->nombre, 
            $m->esta_activo, 
            $m->es_para_natural,
            $m->es_para_juridica
        );
    }
}
""")

write('Infrastructure/Providers/TipoDocumentoIdentidadServiceProvider.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Providers;

use Illuminate\\Support\\ServiceProvider;
use App\\Modules\\TipoDocumentoIdentidad\\Domain\\Contracts\\TipoDocumentoIdentidadRepositoryInterface;
use App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Persistence\\Repositories\\EloquentTipoDocumentoIdentidadRepository;

class TipoDocumentoIdentidadServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TipoDocumentoIdentidadRepositoryInterface::class, EloquentTipoDocumentoIdentidadRepository::class);
    }
    public function boot(): void {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
""")

write('Presentation/Controllers/TipoDocumentoIdentidadController.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Controllers;

use App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases\\ListarTiposDocumentoIdentidadUseCase;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases\\ObtenerTipoDocumentoIdentidadUseCase;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases\\CrearTipoDocumentoIdentidadUseCase;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases\\ActualizarTipoDocumentoIdentidadUseCase;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\UseCases\\EliminarTipoDocumentoIdentidadUseCase;
use App\\Modules\\TipoDocumentoIdentidad\\Application\\DTOs\\GuardarTipoDocumentoIdentidadDTO;
use App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Requests\\GuardarTipoDocumentoIdentidadRequest;
use App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Resources\\TipoDocumentoIdentidadResource;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;

class TipoDocumentoIdentidadController {
    public function __construct(
        private readonly ListarTiposDocumentoIdentidadUseCase $listar,
        private readonly ObtenerTipoDocumentoIdentidadUseCase $obtener,
        private readonly CrearTipoDocumentoIdentidadUseCase $crear,
        private readonly ActualizarTipoDocumentoIdentidadUseCase $actualizar,
        private readonly EliminarTipoDocumentoIdentidadUseCase $eliminar
    ) {}

    public function index(Request $request): JsonResponse {
        $tipoPersona = $request->query('tipo_persona');
        if (!in_array($tipoPersona, ['N', 'J'], true)) {
            return new JsonResponse(
                ['error' => "El parámetro 'tipo_persona' es obligatorio."],
                400
            );
        }

        $items = $this->listar->execute($tipoPersona);
        return new JsonResponse(TipoDocumentoIdentidadResource::collection($items));
    }

    public function show(int $id): JsonResponse {
        return new JsonResponse(new TipoDocumentoIdentidadResource($this->obtener->execute($id)));
    }

    public function store(GuardarTipoDocumentoIdentidadRequest $request): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarTipoDocumentoIdentidadDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            esParaNatural: (bool)$request->es_para_natural,
            esParaJuridica: (bool)$request->es_para_juridica
        );
        $entity = $this->crear->execute($dto);
        return new JsonResponse(new TipoDocumentoIdentidadResource($entity), 201);
    }

    public function update(GuardarTipoDocumentoIdentidadRequest $request, int $id): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new GuardarTipoDocumentoIdentidadDTO(
            codigo: $request->codigo, 
            nombre: $request->nombre, 
            estaActivo: (bool)$request->esta_activo,
            esParaNatural: (bool)$request->es_para_natural,
            esParaJuridica: (bool)$request->es_para_juridica
        );
        $entity = $this->actualizar->execute($id, $dto);
        return new JsonResponse(new TipoDocumentoIdentidadResource($entity));
    }

    public function destroy(int $id): JsonResponse {
        $this->eliminar->execute($id);
        return new JsonResponse(null, 204);
    }
}
""")

write('Presentation/Requests/GuardarTipoDocumentoIdentidadRequest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class GuardarTipoDocumentoIdentidadRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'codigo' => 'required|string|max:20',
            'nombre' => 'required|string|max:100',
            'esta_activo' => 'required|boolean',
            'es_para_natural' => 'required|boolean',
            'es_para_juridica' => 'required|boolean'
        ];
    }
}
""")

write('Presentation/Resources/TipoDocumentoIdentidadResource.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

/**
 * @property-read \\App\\Modules\\TipoDocumentoIdentidad\\Domain\\Entities\\TipoDocumentoIdentidad $resource
 */
class TipoDocumentoIdentidadResource extends JsonResource {
    /** @return array<string, mixed> */
    public function toArray(Request $request): array {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
            'es_para_natural' => $this->resource->esParaNatural,
            'es_para_juridica' => $this->resource->esParaJuridica
        ];
    }
}
""")

write('Presentation/Routes/api.php', """<?php
declare(strict_types=1);
use Illuminate\\Support\\Facades\\Route;
use App\\Modules\\TipoDocumentoIdentidad\\Presentation\\Controllers\\TipoDocumentoIdentidadController;

Route::prefix('api/lf-itse')->middleware('api')->group(function () {
    Route::apiResource('tipos-documento-identidad', TipoDocumentoIdentidadController::class)->parameters([
        'tipos-documento-identidad' => 'id'
    ]);
});
""")

write('Tests/Feature/TipoDocumentoIdentidadTest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoDocumentoIdentidad\\Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;
use App\\Modules\\TipoDocumentoIdentidad\\Infrastructure\\Persistence\\Models\\TipoDocumentoIdentidadModel;
use App\\Models\\User;
use Illuminate\\Support\\Facades\\DB;

class TipoDocumentoIdentidadTest extends TestCase {
    use RefreshDatabase;

    private function getAuthUser(): User {
        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        return User::find($id);
    }

    public function test_rechaza_lista_sin_query_param(): void {
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad');
        $response->assertStatus(400)
                 ->assertExactJson(["error" => "El parámetro 'tipo_persona' es obligatorio."]);
    }

    public function test_rechaza_lista_con_query_param_invalido(): void {
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=X');
        $response->assertStatus(400)
                 ->assertExactJson(["error" => "El parámetro 'tipo_persona' es obligatorio."]);
    }

    public function test_puede_listar_naturales(): void {
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'DNI', 'nombre' => 'DNI Test', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false
        ]);
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'RUC', 'nombre' => 'RUC Test', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true
        ]);
        
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=N');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals('DNI', $response->json()[0]['codigo']);
    }

    public function test_puede_listar_juridicas(): void {
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'DNI', 'nombre' => 'DNI Test', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => false
        ]);
        TipoDocumentoIdentidadModel::create([
            'codigo' => 'RUC', 'nombre' => 'RUC Test', 'esta_activo' => true, 'es_para_natural' => false, 'es_para_juridica' => true
        ]);
        
        $response = $this->getJson('/api/lf-itse/tipos-documento-identidad?tipo_persona=J');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals('RUC', $response->json()[0]['codigo']);
    }

    public function test_puede_crear_tipo(): void {
        $data = ['codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/tipos-documento-identidad', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_documento_identidad', ['codigo' => 'NEW']);
    }

    public function test_puede_actualizar_tipo(): void {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true
        ]);
        $data = ['codigo' => 'UPD', 'nombre' => 'Updated Doc', 'esta_activo' => false, 'es_para_natural' => true, 'es_para_juridica' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/tipos-documento-identidad/' . $m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_documento_identidad', ['codigo' => 'UPD']);
    }

    public function test_puede_eliminar_tipo(): void {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => 'NEW', 'nombre' => 'New Doc', 'esta_activo' => true, 'es_para_natural' => true, 'es_para_juridica' => true
        ]);
        $response = $this->deleteJson('/api/lf-itse/tipos-documento-identidad/' . $m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_documento_identidad', ['id' => $m->id]);
    }
}
""")

print("TipoDocumentoIdentidad generated!")
