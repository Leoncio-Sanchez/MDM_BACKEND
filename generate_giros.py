import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/Giros'
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

write('Domain/Entities/Giro.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Domain\\Entities;

final readonly class Giro {
    public function __construct(
        public ?int $id,
        public ?int $ciiuId,
        public string $nombre,
        public bool $estaActivo,
        public ?\\DateTimeImmutable $fechaDigitacion,
        public int $usuarioId
    ) {}

    public static function crear(?int $ciiuId, string $nombre, bool $estaActivo, int $usuarioId): self {
        return new self(
            id: null,
            ciiuId: $ciiuId,
            nombre: trim($nombre),
            estaActivo: $estaActivo,
            fechaDigitacion: new \\DateTimeImmutable(),
            usuarioId: $usuarioId
        );
    }

    public function actualizar(?int $ciiuId, ?string $nombre, ?bool $estaActivo, int $usuarioId): self {
        return new self(
            id: $this->id,
            ciiuId: $ciiuId !== null ? $ciiuId : $this->ciiuId,
            nombre: $nombre ?? $this->nombre,
            estaActivo: $estaActivo ?? $this->estaActivo,
            fechaDigitacion: $this->fechaDigitacion,
            usuarioId: $usuarioId
        );
    }
}
""")

write('Domain/Exceptions/GiroNoEncontrado.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Domain\\Exceptions;

class GiroNoEncontrado extends \\DomainException {
    public function __construct() {
        parent::__construct('Giro no encontrado');
    }
}
""")

write('Domain/Contracts/GiroRepositoryInterface.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Domain\\Contracts;

use App\\Modules\\Giros\\Domain\\Entities\\Giro;

interface GiroRepositoryInterface {
    /** @return Giro[] */
    public function obtenerTodos(): array;
    public function buscarPorId(int $id): ?Giro;
    public function guardar(Giro $entity): Giro;
    public function actualizar(Giro $entity): Giro;
    public function eliminar(int $id): void;
}
""")

write('Application/DTOs/CrearGiroDTO.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\DTOs;

final readonly class CrearGiroDTO {
    public function __construct(
        public ?int $ciiuId,
        public string $nombre,
        public bool $estaActivo,
        public int $usuarioId
    ) {}
}
""")

write('Application/DTOs/ActualizarGiroDTO.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\DTOs;

final readonly class ActualizarGiroDTO {
    public function __construct(
        public ?int $ciiuId,
        public ?string $nombre,
        public ?bool $estaActivo,
        public int $usuarioId
    ) {}
}
""")

write('Application/UseCases/ListarGirosUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\UseCases;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;

final readonly class ListarGirosUseCase {
    public function __construct(private GiroRepositoryInterface $repository) {}
    /** @return \\App\\Modules\\Giros\\Domain\\Entities\\Giro[] */
    public function execute(): array {
        return $this->repository->obtenerTodos();
    }
}
""")

write('Application/UseCases/ObtenerGiroUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\UseCases;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;
use App\\Modules\\Giros\\Domain\\Exceptions\\GiroNoEncontrado;
use App\\Modules\\Giros\\Domain\\Entities\\Giro;

final readonly class ObtenerGiroUseCase {
    public function __construct(private GiroRepositoryInterface $repository) {}
    public function execute(int $id): Giro {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new GiroNoEncontrado();
        return $entity;
    }
}
""")

write('Application/UseCases/CrearGiroUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\UseCases;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;
use App\\Modules\\Giros\\Domain\\Entities\\Giro;
use App\\Modules\\Giros\\Application\\DTOs\\CrearGiroDTO;

final readonly class CrearGiroUseCase {
    public function __construct(private GiroRepositoryInterface $repository) {}
    public function execute(CrearGiroDTO $dto): Giro {
        $entity = Giro::crear($dto->ciiuId, $dto->nombre, $dto->estaActivo, $dto->usuarioId);
        return $this->repository->guardar($entity);
    }
}
""")

write('Application/UseCases/ActualizarGiroUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\UseCases;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;
use App\\Modules\\Giros\\Domain\\Entities\\Giro;
use App\\Modules\\Giros\\Domain\\Exceptions\\GiroNoEncontrado;
use App\\Modules\\Giros\\Application\\DTOs\\ActualizarGiroDTO;

final readonly class ActualizarGiroUseCase {
    public function __construct(private GiroRepositoryInterface $repository) {}
    public function execute(int $id, ActualizarGiroDTO $dto): Giro {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new GiroNoEncontrado();
        $updated = $entity->actualizar($dto->ciiuId, $dto->nombre, $dto->estaActivo, $dto->usuarioId);
        return $this->repository->actualizar($updated);
    }
}
""")

write('Application/UseCases/EliminarGiroUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Application\\UseCases;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;

final readonly class EliminarGiroUseCase {
    public function __construct(private GiroRepositoryInterface $repository) {}
    public function execute(int $id): void {
        $this->repository->eliminar($id);
    }
}
""")

write('Infrastructure/Persistence/Models/GiroModel.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Infrastructure\\Persistence\\Models;

use Illuminate\\Database\\Eloquent\\Model;

/**
 * @property int $id
 * @property int|null $ciiu_id
 * @property string $nombre
 * @property bool $esta_activo
 * @property \\Carbon\\Carbon|null $fecha_digitacion
 * @property int $usuario_id
 */
class GiroModel extends Model {
    protected $table = 'giros';
    public $timestamps = false;
    protected $fillable = ['ciiu_id', 'nombre', 'esta_activo', 'fecha_digitacion', 'usuario_id'];
    protected $casts = [
        'esta_activo' => 'boolean',
        'fecha_digitacion' => 'datetime'
    ];
}
""")

write('Infrastructure/Persistence/Repositories/EloquentGiroRepository.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Infrastructure\\Persistence\\Repositories;

use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;
use App\\Modules\\Giros\\Domain\\Entities\\Giro;
use App\\Modules\\Giros\\Infrastructure\\Persistence\\Models\\GiroModel;

class EloquentGiroRepository implements GiroRepositoryInterface {
    /** @return Giro[] */
    public function obtenerTodos(): array {
        return GiroModel::orderBy('id')->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }
    public function buscarPorId(int $id): ?Giro {
        $m = GiroModel::find($id);
        return $m ? $this->toDomain($m) : null;
    }
    public function guardar(Giro $entity): Giro {
        $m = GiroModel::create([
            'ciiu_id' => $entity->ciiuId,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'fecha_digitacion' => $entity->fechaDigitacion?->format('Y-m-d H:i:s.u'),
            'usuario_id' => $entity->usuarioId
        ]);
        return $this->toDomain($m);
    }
    public function actualizar(Giro $entity): Giro {
        $m = GiroModel::findOrFail($entity->id);
        $m->update([
            'ciiu_id' => $entity->ciiuId,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'usuario_id' => $entity->usuarioId
        ]);
        return $this->toDomain($m);
    }
    public function eliminar(int $id): void {
        GiroModel::destroy($id);
    }
    private function toDomain(GiroModel $m): Giro {
        return new Giro(
            $m->id, 
            $m->ciiu_id, 
            $m->nombre, 
            $m->esta_activo, 
            $m->fecha_digitacion ? new \\DateTimeImmutable($m->fecha_digitacion->format('Y-m-d H:i:s.u')) : null,
            $m->usuario_id
        );
    }
}
""")

write('Infrastructure/Providers/GirosServiceProvider.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Infrastructure\\Providers;

use Illuminate\\Support\\ServiceProvider;
use App\\Modules\\Giros\\Domain\\Contracts\\GiroRepositoryInterface;
use App\\Modules\\Giros\\Infrastructure\\Persistence\\Repositories\\EloquentGiroRepository;

class GirosServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(GiroRepositoryInterface::class, EloquentGiroRepository::class);
    }
    public function boot(): void {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
""")

write('Presentation/Controllers/GiroController.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Presentation\\Controllers;

use App\\Modules\\Giros\\Application\\UseCases\\ListarGirosUseCase;
use App\\Modules\\Giros\\Application\\UseCases\\ObtenerGiroUseCase;
use App\\Modules\\Giros\\Application\\UseCases\\CrearGiroUseCase;
use App\\Modules\\Giros\\Application\\UseCases\\ActualizarGiroUseCase;
use App\\Modules\\Giros\\Application\\UseCases\\EliminarGiroUseCase;
use App\\Modules\\Giros\\Application\\DTOs\\CrearGiroDTO;
use App\\Modules\\Giros\\Application\\DTOs\\ActualizarGiroDTO;
use App\\Modules\\Giros\\Presentation\\Requests\\CrearGiroRequest;
use App\\Modules\\Giros\\Presentation\\Requests\\ActualizarGiroRequest;
use App\\Modules\\Giros\\Presentation\\Resources\\GiroResource;
use Illuminate\\Http\\JsonResponse;

class GiroController {
    public function __construct(
        private readonly ListarGirosUseCase $listar,
        private readonly ObtenerGiroUseCase $obtener,
        private readonly CrearGiroUseCase $crear,
        private readonly ActualizarGiroUseCase $actualizar,
        private readonly EliminarGiroUseCase $eliminar
    ) {}

    public function index(): JsonResponse {
        return new JsonResponse(GiroResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse {
        return new JsonResponse(new GiroResource($this->obtener->execute($id)));
    }

    public function store(CrearGiroRequest $request): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new CrearGiroDTO(
            ciiuId: $request->ciiu_id !== null ? (int)$request->ciiu_id : null,
            nombre: $request->nombre,
            estaActivo: (bool)$request->esta_activo,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);
        return new JsonResponse(new GiroResource($entity), 201);
    }

    public function update(ActualizarGiroRequest $request, int $id): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new ActualizarGiroDTO(
            ciiuId: $request->ciiu_id !== null ? (int)$request->ciiu_id : null,
            nombre: $request->nombre,
            estaActivo: $request->has('esta_activo') ? (bool)$request->esta_activo : null,
            usuarioId: $user->id
        );
        $entity = $this->actualizar->execute($id, $dto);
        return new JsonResponse(new GiroResource($entity));
    }

    public function destroy(int $id): JsonResponse {
        $this->eliminar->execute($id);
        return new JsonResponse(null, 204);
    }
}
""")

write('Presentation/Requests/CrearGiroRequest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Presentation\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class CrearGiroRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'ciiu_id' => 'nullable|integer',
            'nombre' => 'required|string|max:200',
            'esta_activo' => 'required|boolean'
        ];
    }
}
""")

write('Presentation/Requests/ActualizarGiroRequest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Presentation\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class ActualizarGiroRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'ciiu_id' => 'nullable|integer',
            'nombre' => 'nullable|string|max:200',
            'esta_activo' => 'nullable|boolean'
        ];
    }
}
""")

write('Presentation/Resources/GiroResource.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Presentation\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

/**
 * @property-read \\App\\Modules\\Giros\\Domain\\Entities\\Giro $resource
 */
class GiroResource extends JsonResource {
    /** @return array<string, mixed> */
    public function toArray(Request $request): array {
        return [
            'id' => $this->resource->id,
            'ciiu_id' => $this->resource->ciiuId,
            'nombre' => $this->resource->nombre,
            'esta_activo' => $this->resource->estaActivo,
            'fecha_digitacion' => $this->resource->fechaDigitacion?->setTimezone(new \\DateTimeZone('America/Lima'))->format('Y-m-d\\TH:i:s.uP'),
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
""")

write('Presentation/Routes/api.php', """<?php
declare(strict_types=1);
use Illuminate\\Support\\Facades\\Route;
use App\\Modules\\Giros\\Presentation\\Controllers\\GiroController;

Route::prefix('api/lf-itse')->middleware('api')->group(function () {
    Route::apiResource('giros', GiroController::class);
});
""")

write('Tests/Feature/GiroTest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\Giros\\Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;
use App\\Modules\\Giros\\Infrastructure\\Persistence\\Models\\GiroModel;
use App\\Models\\User;
use Illuminate\\Support\\Facades\\DB;

class GiroTest extends TestCase {
    use RefreshDatabase;

    private function getAuthUser(): User {
        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        /** @var User $user */
        $user = User::find($id);
        return $user;
    }

    public function test_puede_listar_giros(): void {
        GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        
        $response = $this->getJson('/api/lf-itse/giros');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_obtener_giro(): void {
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->getJson('/api/lf-itse/giros/' . $m->id);
        $response->assertStatus(200);
        $this->assertEquals(1234, $response->json('ciiu_id'));
    }

    public function test_rechaza_crear_giro_sin_auth(): void {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->postJson('/api/lf-itse/giros', $data);
        $response->assertStatus(401);
    }

    public function test_puede_crear_giro_con_auth(): void {
        $data = ['ciiu_id' => 5678, 'nombre' => 'Giro auth', 'esta_activo' => true];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/giros', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 5678, 'usuario_id' => 1]); // the inserted user is id 1
    }

    public function test_puede_actualizar_giro(): void {
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $data = ['ciiu_id' => 9999, 'nombre' => 'Giro Upd', 'esta_activo' => false];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/giros/' . $m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('giros', ['ciiu_id' => 9999]);
    }

    public function test_puede_eliminar_giro(): void {
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->deleteJson('/api/lf-itse/giros/' . $m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('giros', ['id' => $m->id]);
    }

    public function test_rechaza_actualizar_sin_auth(): void {
        $m = GiroModel::create([
            'ciiu_id' => 1234, 'nombre' => 'Test Giro', 'esta_activo' => true, 'fecha_digitacion' => '2026-04-14 13:41:31', 'usuario_id' => 1
        ]);
        $response = $this->putJson('/api/lf-itse/giros/' . $m->id, []);
        $response->assertStatus(401);
    }
}
""")

print("Giros generated!")
