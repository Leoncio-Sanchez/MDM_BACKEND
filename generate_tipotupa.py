import os

base = '/home/leo/Documentos/Muni/backend-php/app/Modules/TipoProcedimientoTupa'
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

write('Domain/Entities/TipoProcedimientoTupa.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities;

final readonly class TipoProcedimientoTupa {
    public function __construct(
        public ?int $id,
        public string $codigo,
        public string $nombre,
        public int $plazoAtencionDias,
        public int $diasAlertaVencimiento,
        public bool $estaActivo,
        public bool $requiereLf,
        public bool $requiereItse,
        public \\DateTimeImmutable $fechaDigitacion,
        public int $unidadOrganicaId,
        public int $usuarioId
    ) {}

    public static function crear(
        string $codigo,
        string $nombre,
        int $plazoAtencionDias,
        int $diasAlertaVencimiento,
        bool $requiereLf,
        bool $requiereItse,
        int $unidadOrganicaId,
        int $usuarioId
    ): self {
        return new self(
            id: null,
            codigo: strtoupper(trim($codigo)),
            nombre: trim($nombre),
            plazoAtencionDias: $plazoAtencionDias,
            diasAlertaVencimiento: $diasAlertaVencimiento,
            estaActivo: true,
            requiereLf: $requiereLf,
            requiereItse: $requiereItse,
            fechaDigitacion: new \\DateTimeImmutable(),
            unidadOrganicaId: $unidadOrganicaId,
            usuarioId: $usuarioId
        );
    }

    public function actualizar(
        ?string $codigo = null,
        ?string $nombre = null,
        ?int $plazoAtencionDias = null,
        ?int $diasAlertaVencimiento = null,
        ?bool $estaActivo = null,
        ?bool $requiereLf = null,
        ?bool $requiereItse = null,
        ?int $unidadOrganicaId = null
    ): self {
        return new self(
            id: $this->id,
            codigo: $codigo !== null ? strtoupper(trim($codigo)) : $this->codigo,
            nombre: $nombre ?? $this->nombre,
            plazoAtencionDias: $plazoAtencionDias ?? $this->plazoAtencionDias,
            diasAlertaVencimiento: $diasAlertaVencimiento ?? $this->diasAlertaVencimiento,
            estaActivo: $estaActivo ?? $this->estaActivo,
            requiereLf: $requiereLf ?? $this->requiereLf,
            requiereItse: $requiereItse ?? $this->requiereItse,
            fechaDigitacion: $this->fechaDigitacion,
            unidadOrganicaId: $unidadOrganicaId ?? $this->unidadOrganicaId,
            usuarioId: $this->usuarioId
        );
    }
}
""")

write('Domain/Exceptions/TipoProcedimientoTupaNoEncontrado.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Domain\\Exceptions;

class TipoProcedimientoTupaNoEncontrado extends \\DomainException {
    public function __construct() {
        parent::__construct('TipoProcedimientoTupa no encontrado');
    }
}
""")

write('Domain/Contracts/TipoProcedimientoTupaRepositoryInterface.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa;

interface TipoProcedimientoTupaRepositoryInterface {
    /** @return TipoProcedimientoTupa[] */
    public function obtenerTodos(): array;
    public function buscarPorId(int $id): ?TipoProcedimientoTupa;
    public function guardar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa;
    public function actualizar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa;
    public function eliminar(int $id): void;
}
""")

write('Application/DTOs/CrearTipoProcedimientoTupaDTO.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs;

final readonly class CrearTipoProcedimientoTupaDTO {
    public function __construct(
        public string $codigo,
        public string $nombre,
        public int $plazoAtencionDias,
        public int $diasAlertaVencimiento,
        public bool $requiereLf,
        public bool $requiereItse,
        public int $unidadOrganicaId,
        public int $usuarioId
    ) {}
}
""")

write('Application/DTOs/ActualizarTipoProcedimientoTupaDTO.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs;

final readonly class ActualizarTipoProcedimientoTupaDTO {
    public function __construct(
        public ?string $codigo,
        public ?string $nombre,
        public ?int $plazoAtencionDias,
        public ?int $diasAlertaVencimiento,
        public ?bool $estaActivo,
        public ?bool $requiereLf,
        public ?bool $requiereItse,
        public ?int $unidadOrganicaId
    ) {}
}
""")

write('Application/UseCases/ListarTiposProcedimientoTupaUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;

final readonly class ListarTiposProcedimientoTupaUseCase {
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}
    /** @return \\App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa[] */
    public function execute(): array {
        return $this->repository->obtenerTodos();
    }
}
""")

write('Application/UseCases/ObtenerTipoProcedimientoTupaUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Exceptions\\TipoProcedimientoTupaNoEncontrado;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa;

final readonly class ObtenerTipoProcedimientoTupaUseCase {
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}
    public function execute(int $id): TipoProcedimientoTupa {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new TipoProcedimientoTupaNoEncontrado();
        return $entity;
    }
}
""")

write('Application/UseCases/CrearTipoProcedimientoTupaUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa;
use App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs\\CrearTipoProcedimientoTupaDTO;

final readonly class CrearTipoProcedimientoTupaUseCase {
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}
    public function execute(CrearTipoProcedimientoTupaDTO $dto): TipoProcedimientoTupa {
        $entity = TipoProcedimientoTupa::crear(
            $dto->codigo, 
            $dto->nombre, 
            $dto->plazoAtencionDias, 
            $dto->diasAlertaVencimiento, 
            $dto->requiereLf, 
            $dto->requiereItse, 
            $dto->unidadOrganicaId, 
            $dto->usuarioId
        );
        return $this->repository->guardar($entity);
    }
}
""")

write('Application/UseCases/ActualizarTipoProcedimientoTupaUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Exceptions\\TipoProcedimientoTupaNoEncontrado;
use App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs\\ActualizarTipoProcedimientoTupaDTO;

final readonly class ActualizarTipoProcedimientoTupaUseCase {
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}
    public function execute(int $id, ActualizarTipoProcedimientoTupaDTO $dto): TipoProcedimientoTupa {
        $entity = $this->repository->buscarPorId($id);
        if (!$entity) throw new TipoProcedimientoTupaNoEncontrado();
        $updated = $entity->actualizar(
            $dto->codigo,
            $dto->nombre,
            $dto->plazoAtencionDias,
            $dto->diasAlertaVencimiento,
            $dto->estaActivo,
            $dto->requiereLf,
            $dto->requiereItse,
            $dto->unidadOrganicaId
        );
        return $this->repository->actualizar($updated);
    }
}
""")

write('Application/UseCases/EliminarTipoProcedimientoTupaUseCase.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;

final readonly class EliminarTipoProcedimientoTupaUseCase {
    public function __construct(private TipoProcedimientoTupaRepositoryInterface $repository) {}
    public function execute(int $id): void {
        $this->repository->eliminar($id);
    }
}
""")

write('Infrastructure/Persistence/Models/TipoProcedimientoTupaModel.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Persistence\\Models;

use Illuminate\\Database\\Eloquent\\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property int $plazo_atencion_dias
 * @property int $dias_alerta_vencimiento
 * @property bool $esta_activo
 * @property bool $requiere_lf
 * @property bool $requiere_itse
 * @property \\Carbon\\Carbon $fecha_digitacion
 * @property int $unidad_organica_id
 * @property int $usuario_id
 */
class TipoProcedimientoTupaModel extends Model {
    protected $table = 'tipos_procedimiento_tupa';
    public $timestamps = false;
    protected $fillable = [
        'codigo', 'nombre', 'plazo_atencion_dias', 'dias_alerta_vencimiento', 
        'esta_activo', 'requiere_lf', 'requiere_itse', 'fecha_digitacion', 
        'unidad_organica_id', 'usuario_id'
    ];
    protected $casts = [
        'esta_activo' => 'boolean',
        'requiere_lf' => 'boolean',
        'requiere_itse' => 'boolean',
        'fecha_digitacion' => 'datetime'
    ];
}
""")

write('Infrastructure/Persistence/Repositories/EloquentTipoProcedimientoTupaRepository.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Persistence\\Repositories;

use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa;
use App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Persistence\\Models\\TipoProcedimientoTupaModel;

class EloquentTipoProcedimientoTupaRepository implements TipoProcedimientoTupaRepositoryInterface {
    /** @return TipoProcedimientoTupa[] */
    public function obtenerTodos(): array {
        return TipoProcedimientoTupaModel::orderBy('nombre')->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }
    public function buscarPorId(int $id): ?TipoProcedimientoTupa {
        $m = TipoProcedimientoTupaModel::find($id);
        return $m ? $this->toDomain($m) : null;
    }
    public function guardar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa {
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'plazo_atencion_dias' => $entity->plazoAtencionDias,
            'dias_alerta_vencimiento' => $entity->diasAlertaVencimiento,
            'esta_activo' => $entity->estaActivo,
            'requiere_lf' => $entity->requiereLf,
            'requiere_itse' => $entity->requiereItse,
            'fecha_digitacion' => $entity->fechaDigitacion->format('Y-m-d H:i:s.u'),
            'unidad_organica_id' => $entity->unidadOrganicaId,
            'usuario_id' => $entity->usuarioId
        ]);
        return $this->toDomain($m);
    }
    public function actualizar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa {
        $m = TipoProcedimientoTupaModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'plazo_atencion_dias' => $entity->plazoAtencionDias,
            'dias_alerta_vencimiento' => $entity->diasAlertaVencimiento,
            'esta_activo' => $entity->estaActivo,
            'requiere_lf' => $entity->requiereLf,
            'requiere_itse' => $entity->requiereItse,
            'unidad_organica_id' => $entity->unidadOrganicaId
        ]);
        return $this->toDomain($m);
    }
    public function eliminar(int $id): void {
        TipoProcedimientoTupaModel::destroy($id);
    }
    private function toDomain(TipoProcedimientoTupaModel $m): TipoProcedimientoTupa {
        return new TipoProcedimientoTupa(
            $m->id, 
            $m->codigo, 
            $m->nombre, 
            $m->plazo_atencion_dias, 
            $m->dias_alerta_vencimiento, 
            $m->esta_activo, 
            $m->requiere_lf, 
            $m->requiere_itse, 
            new \\DateTimeImmutable($m->fecha_digitacion->format('Y-m-d H:i:s.u')),
            $m->unidad_organica_id, 
            $m->usuario_id
        );
    }
}
""")

write('Infrastructure/Providers/TipoProcedimientoTupaServiceProvider.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Providers;

use Illuminate\\Support\\ServiceProvider;
use App\\Modules\\TipoProcedimientoTupa\\Domain\\Contracts\\TipoProcedimientoTupaRepositoryInterface;
use App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Persistence\\Repositories\\EloquentTipoProcedimientoTupaRepository;

class TipoProcedimientoTupaServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TipoProcedimientoTupaRepositoryInterface::class, EloquentTipoProcedimientoTupaRepository::class);
    }
    public function boot(): void {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
""")

write('Presentation/Controllers/TipoProcedimientoTupaController.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Presentation\\Controllers;

use App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases\\ListarTiposProcedimientoTupaUseCase;
use App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases\\ObtenerTipoProcedimientoTupaUseCase;
use App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases\\CrearTipoProcedimientoTupaUseCase;
use App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases\\ActualizarTipoProcedimientoTupaUseCase;
use App\\Modules\\TipoProcedimientoTupa\\Application\\UseCases\\EliminarTipoProcedimientoTupaUseCase;
use App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs\\CrearTipoProcedimientoTupaDTO;
use App\\Modules\\TipoProcedimientoTupa\\Application\\DTOs\\ActualizarTipoProcedimientoTupaDTO;
use App\\Modules\\TipoProcedimientoTupa\\Presentation\\Requests\\CrearTipoProcedimientoTupaRequest;
use App\\Modules\\TipoProcedimientoTupa\\Presentation\\Requests\\ActualizarTipoProcedimientoTupaRequest;
use App\\Modules\\TipoProcedimientoTupa\\Presentation\\Resources\\TipoProcedimientoTupaResource;
use Illuminate\\Http\\JsonResponse;

class TipoProcedimientoTupaController {
    public function __construct(
        private readonly ListarTiposProcedimientoTupaUseCase $listar,
        private readonly ObtenerTipoProcedimientoTupaUseCase $obtener,
        private readonly CrearTipoProcedimientoTupaUseCase $crear,
        private readonly ActualizarTipoProcedimientoTupaUseCase $actualizar,
        private readonly EliminarTipoProcedimientoTupaUseCase $eliminar
    ) {}

    public function index(): JsonResponse {
        return new JsonResponse(TipoProcedimientoTupaResource::collection($this->listar->execute()));
    }

    public function show(int $id): JsonResponse {
        return new JsonResponse(new TipoProcedimientoTupaResource($this->obtener->execute($id)));
    }

    public function store(CrearTipoProcedimientoTupaRequest $request): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new CrearTipoProcedimientoTupaDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            plazoAtencionDias: (int)$request->plazo_atencion_dias,
            diasAlertaVencimiento: (int)$request->dias_alerta_vencimiento,
            requiereLf: $request->has('requiere_lf') ? (bool)$request->requiere_lf : false,
            requiereItse: $request->has('requiere_itse') ? (bool)$request->requiere_itse : false,
            unidadOrganicaId: (int)$request->unidad_organica_id,
            usuarioId: $user->id
        );
        $entity = $this->crear->execute($dto);
        return new JsonResponse(new TipoProcedimientoTupaResource($entity), 201);
    }

    public function update(ActualizarTipoProcedimientoTupaRequest $request, int $id): JsonResponse {
        $user = $request->user('api');
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthenticated'], 401);
        }

        $dto = new ActualizarTipoProcedimientoTupaDTO(
            codigo: $request->codigo,
            nombre: $request->nombre,
            plazoAtencionDias: $request->has('plazo_atencion_dias') ? (int)$request->plazo_atencion_dias : null,
            diasAlertaVencimiento: $request->has('dias_alerta_vencimiento') ? (int)$request->dias_alerta_vencimiento : null,
            estaActivo: $request->has('esta_activo') ? (bool)$request->esta_activo : null,
            requiereLf: $request->has('requiere_lf') ? (bool)$request->requiere_lf : null,
            requiereItse: $request->has('requiere_itse') ? (bool)$request->requiere_itse : null,
            unidadOrganicaId: $request->has('unidad_organica_id') ? (int)$request->unidad_organica_id : null
        );
        $entity = $this->actualizar->execute($id, $dto);
        return new JsonResponse(new TipoProcedimientoTupaResource($entity));
    }

    public function destroy(int $id): JsonResponse {
        $this->eliminar->execute($id);
        return new JsonResponse(null, 204);
    }
}
""")

write('Presentation/Requests/CrearTipoProcedimientoTupaRequest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Presentation\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class CrearTipoProcedimientoTupaRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'codigo' => 'required|string|max:50',
            'nombre' => 'required|string|max:250',
            'plazo_atencion_dias' => 'required|integer|min:0',
            'dias_alerta_vencimiento' => 'required|integer|min:0',
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'required|integer'
        ];
    }
}
""")

write('Presentation/Requests/ActualizarTipoProcedimientoTupaRequest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Presentation\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class ActualizarTipoProcedimientoTupaRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'codigo' => 'nullable|string|max:50',
            'nombre' => 'nullable|string|max:250',
            'plazo_atencion_dias' => 'nullable|integer|min:0',
            'dias_alerta_vencimiento' => 'nullable|integer|min:0',
            'esta_activo' => 'nullable|boolean',
            'requiere_lf' => 'nullable|boolean',
            'requiere_itse' => 'nullable|boolean',
            'unidad_organica_id' => 'nullable|integer'
        ];
    }
}
""")

write('Presentation/Resources/TipoProcedimientoTupaResource.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Presentation\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

/**
 * @property-read \\App\\Modules\\TipoProcedimientoTupa\\Domain\\Entities\\TipoProcedimientoTupa $resource
 */
class TipoProcedimientoTupaResource extends JsonResource {
    /** @return array<string, mixed> */
    public function toArray(Request $request): array {
        return [
            'id' => $this->resource->id,
            'codigo' => $this->resource->codigo,
            'nombre' => $this->resource->nombre,
            'plazo_atencion_dias' => $this->resource->plazoAtencionDias,
            'dias_alerta_vencimiento' => $this->resource->diasAlertaVencimiento,
            'esta_activo' => $this->resource->estaActivo,
            'requiere_lf' => $this->resource->requiereLf,
            'requiere_itse' => $this->resource->requiereItse,
            'fecha_digitacion' => $this->resource->fechaDigitacion?->setTimezone(new \\DateTimeZone('America/Lima'))->format('Y-m-d\\TH:i:s.uP'),
            'unidad_organica' => $this->resource->unidadOrganicaId,
            'usuario' => $this->resource->usuarioId,
        ];
    }
}
""")

write('Presentation/Routes/api.php', """<?php
declare(strict_types=1);
use Illuminate\\Support\\Facades\\Route;
use App\\Modules\\TipoProcedimientoTupa\\Presentation\\Controllers\\TipoProcedimientoTupaController;

Route::prefix('api/lf-itse')->middleware('api')->group(function () {
    Route::apiResource('tipos-procedimiento-tupa', TipoProcedimientoTupaController::class)->parameters([
        'tipos-procedimiento-tupa' => 'id'
    ]);
});
""")

write('Tests/Feature/TipoProcedimientoTupaTest.php', """<?php
declare(strict_types=1);
namespace App\\Modules\\TipoProcedimientoTupa\\Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;
use App\\Modules\\TipoProcedimientoTupa\\Infrastructure\\Persistence\\Models\\TipoProcedimientoTupaModel;
use App\\Models\\User;
use Illuminate\\Support\\Facades\\DB;

class TipoProcedimientoTupaTest extends TestCase {
    use RefreshDatabase;

    private function getAuthUser(): User {
        /** @var User|null $existing */
        $existing = User::where('username', 'test_user')->first();
        if ($existing) return $existing;

        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        /** @var User $user */
        $user = User::find($id);
        return $user;
    }

    private function seedDeps(): void {
        DB::table('unidades_organicas')->insert([
            'id' => 1, 'codigo' => 'U01', 'nombre' => 'Unidad 1', 'esta_activo' => true
        ]);
    }

    public function test_puede_listar_tipos(): void {
        $this->getAuthUser();
        $this->seedDeps();
        TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_obtener_tipo(): void {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa/' . $m->id);
        $response->assertStatus(200);
        $this->assertEquals('T01', $response->json('codigo'));
    }

    public function test_rechaza_crear_tipo_sin_auth(): void {
        $data = [
            'codigo' => 'T02', 'nombre' => 'Tupa auth', 'plazo_atencion_dias' => 10, 
            'dias_alerta_vencimiento' => 2, 'unidad_organica_id' => 1
        ];
        $response = $this->postJson('/api/lf-itse/tipos-procedimiento-tupa', $data);
        $response->assertStatus(401);
    }

    public function test_puede_crear_tipo_con_auth(): void {
        $this->seedDeps();
        $data = [
            'codigo' => 'T02', 'nombre' => 'Tupa auth', 'plazo_atencion_dias' => 10, 
            'dias_alerta_vencimiento' => 2, 'unidad_organica_id' => 1
        ];
        $response = $this->actingAs($this->getAuthUser(), 'api')->postJson('/api/lf-itse/tipos-procedimiento-tupa', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['codigo' => 'T02', 'usuario_id' => 1]);
    }

    public function test_puede_actualizar_tipo(): void {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        $data = ['codigo' => 'T03', 'nombre' => 'Tupa Upd'];
        $response = $this->actingAs($this->getAuthUser(), 'api')->putJson('/api/lf-itse/tipos-procedimiento-tupa/' . $m->id, $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('tipos_procedimiento_tupa', ['codigo' => 'T03']);
    }

    public function test_puede_eliminar_tipo(): void {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        $response = $this->deleteJson('/api/lf-itse/tipos-procedimiento-tupa/' . $m->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tipos_procedimiento_tupa', ['id' => $m->id]);
    }

    public function test_rechaza_actualizar_sin_auth(): void {
        $this->getAuthUser();
        $this->seedDeps();
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        $response = $this->putJson('/api/lf-itse/tipos-procedimiento-tupa/' . $m->id, []);
        $response->assertStatus(401);
    }
    
    public function test_monto_no_aparece_en_json(): void {
        $this->getAuthUser();
        $this->seedDeps();
        TipoProcedimientoTupaModel::create([
            'codigo' => 'T01', 'nombre' => 'Test TUPA', 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 2,
            'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false, 
            'fecha_digitacion' => '2026-04-14 13:41:31', 'unidad_organica_id' => 1, 'usuario_id' => 1
        ]);
        $response = $this->getJson('/api/lf-itse/tipos-procedimiento-tupa');
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('monto', $response->json()[0]);
    }
}
""")

print("TipoProcedimientoTupa generated!")
