<?php
$base = __DIR__ . '/app/Modules/Zonificacion';
@mkdir("$base/Domain/Entities", 0777, true);
@mkdir("$base/Domain/Contracts", 0777, true);
@mkdir("$base/Domain/Exceptions", 0777, true);
@mkdir("$base/Application/UseCases", 0777, true);
@mkdir("$base/Application/DTOs", 0777, true);
@mkdir("$base/Infrastructure/Persistence/Models", 0777, true);
@mkdir("$base/Infrastructure/Persistence/Repositories", 0777, true);
@mkdir("$base/Infrastructure/Providers", 0777, true);
@mkdir("$base/Presentation/Controllers", 0777, true);
@mkdir("$base/Presentation/Requests", 0777, true);
@mkdir("$base/Presentation/Resources", 0777, true);
@mkdir("$base/Presentation/Routes", 0777, true);

// 1. Domain Entity
file_put_contents("$base/Domain/Entities/Zonificacion.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Domain\Entities;

use DateTimeImmutable;

final readonly class Zonificacion {
    public function __construct(
        public ?int \$id,
        public string \$codigo,
        public string \$nombre,
        public bool \$estaActivo,
        public DateTimeImmutable \$fechaDigitacion,
        public int \$usuarioId
    ) {}

    public static function crear(string \$codigo, string \$nombre, bool \$estaActivo, int \$usuarioId): self {
        return new self(
            id: null,
            codigo: strtoupper(trim(\$codigo)),
            nombre: trim(\$nombre),
            estaActivo: \$estaActivo,
            fechaDigitacion: new DateTimeImmutable(),
            usuarioId: \$usuarioId
        );
    }

    public function actualizar(?string \$codigo, ?string \$nombre, ?bool \$estaActivo, ?int \$usuarioId): self {
        return new self(
            id: \$this->id,
            codigo: \$codigo !== null ? strtoupper(trim(\$codigo)) : \$this->codigo,
            nombre: \$nombre ?? \$this->nombre,
            estaActivo: \$estaActivo ?? \$this->estaActivo,
            fechaDigitacion: \$this->fechaDigitacion,
            usuarioId: \$usuarioId ?? \$this->usuarioId
        );
    }
}
");

// 2. Exception
file_put_contents("$base/Domain/Exceptions/ZonificacionNoEncontrado.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Domain\Exceptions;

class ZonificacionNoEncontrado extends \DomainException {
    public function __construct() {
        parent::__construct('Zonificación no encontrada');
    }
}
");

// 3. Contract
file_put_contents("$base/Domain/Contracts/ZonificacionRepositoryInterface.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Domain\Contracts;

use App\Modules\Zonificacion\Domain\Entities\Zonificacion;

interface ZonificacionRepositoryInterface {
    /** @return Zonificacion[] */
    public function obtenerTodos(): array;
    public function buscarPorId(int \$id): ?Zonificacion;
    public function guardar(Zonificacion \$zonificacion): Zonificacion;
    public function actualizar(Zonificacion \$zonificacion): Zonificacion;
    public function eliminar(int \$id): void;
}
");

// 4. Application
file_put_contents("$base/Application/UseCases/ListarZonificacionUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;

final readonly class ListarZonificacionUseCase {
    public function __construct(private ZonificacionRepositoryInterface \$repository) {}
    /** @return \App\Modules\Zonificacion\Domain\Entities\Zonificacion[] */
    public function execute(): array {
        return \$this->repository->obtenerTodos();
    }
}
");

file_put_contents("$base/Application/UseCases/ObtenerZonificacionUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Exceptions\ZonificacionNoEncontrado;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;

final readonly class ObtenerZonificacionUseCase {
    public function __construct(private ZonificacionRepositoryInterface \$repository) {}
    public function execute(int \$id): Zonificacion {
        \$entity = \$this->repository->buscarPorId(\$id);
        if (!\$entity) throw new ZonificacionNoEncontrado();
        return \$entity;
    }
}
");

file_put_contents("$base/Application/DTOs/GuardarZonificacionDTO.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\DTOs;

final readonly class GuardarZonificacionDTO {
    public function __construct(
        public string \$codigo,
        public string \$nombre,
        public bool \$estaActivo,
        public int \$usuarioId
    ) {}
}
");

file_put_contents("$base/Application/UseCases/CrearZonificacionUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;

final readonly class CrearZonificacionUseCase {
    public function __construct(private ZonificacionRepositoryInterface \$repository) {}
    public function execute(GuardarZonificacionDTO \$dto): Zonificacion {
        \$entity = Zonificacion::crear(\$dto->codigo, \$dto->nombre, \$dto->estaActivo, \$dto->usuarioId);
        return \$this->repository->guardar(\$entity);
    }
}
");

file_put_contents("$base/Application/UseCases/ActualizarZonificacionUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Domain\Exceptions\ZonificacionNoEncontrado;
use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;

final readonly class ActualizarZonificacionUseCase {
    public function __construct(private ZonificacionRepositoryInterface \$repository) {}
    public function execute(int \$id, GuardarZonificacionDTO \$dto): Zonificacion {
        \$entity = \$this->repository->buscarPorId(\$id);
        if (!\$entity) throw new ZonificacionNoEncontrado();
        \$updated = \$entity->actualizar(\$dto->codigo, \$dto->nombre, \$dto->estaActivo, \$dto->usuarioId);
        return \$this->repository->actualizar(\$updated);
    }
}
");

file_put_contents("$base/Application/UseCases/EliminarZonificacionUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Application\UseCases;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;

final readonly class EliminarZonificacionUseCase {
    public function __construct(private ZonificacionRepositoryInterface \$repository) {}
    public function execute(int \$id): void {
        \$this->repository->eliminar(\$id);
    }
}
");

// 5. Infra Models
file_put_contents("$base/Infrastructure/Persistence/Models/ZonificacionModel.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ZonificacionModel extends Model {
    protected \$table = 'zonificaciones';
    public \$timestamps = false;
    protected \$fillable = ['codigo', 'nombre', 'esta_activo', 'fecha_digitacion', 'usuario_id'];
    protected \$casts = [
        'esta_activo' => 'boolean',
        'fecha_digitacion' => 'datetime',
        'usuario_id' => 'integer'
    ];
}
");

// 6. Repo
file_put_contents("$base/Infrastructure/Persistence/Repositories/EloquentZonificacionRepository.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Infrastructure\Persistence\Repositories;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Infrastructure\Persistence\Models\ZonificacionModel;
use DateTimeImmutable;

class EloquentZonificacionRepository implements ZonificacionRepositoryInterface {
    /** @return Zonificacion[] */
    public function obtenerTodos(): array {
        return ZonificacionModel::all()->map(fn(\$m) => \$this->toDomain(\$m))->toArray();
    }
    public function buscarPorId(int \$id): ?Zonificacion {
        \$m = ZonificacionModel::find(\$id);
        return \$m ? \$this->toDomain(\$m) : null;
    }
    public function guardar(Zonificacion \$entity): Zonificacion {
        \$m = ZonificacionModel::create([
            'codigo' => \$entity->codigo,
            'nombre' => \$entity->nombre,
            'esta_activo' => \$entity->estaActivo,
            'fecha_digitacion' => \$entity->fechaDigitacion->format('Y-m-d H:i:s.uP'),
            'usuario_id' => \$entity->usuarioId
        ]);
        return \$this->toDomain(\$m);
    }
    public function actualizar(Zonificacion \$entity): Zonificacion {
        \$m = ZonificacionModel::findOrFail(\$entity->id);
        \$m->update([
            'codigo' => \$entity->codigo,
            'nombre' => \$entity->nombre,
            'esta_activo' => \$entity->estaActivo,
            'fecha_digitacion' => \$entity->fechaDigitacion->format('Y-m-d H:i:s.uP'),
            'usuario_id' => \$entity->usuarioId
        ]);
        return \$this->toDomain(\$m);
    }
    public function eliminar(int \$id): void {
        ZonificacionModel::destroy(\$id);
    }
    private function toDomain(ZonificacionModel \$m): Zonificacion {
        return new Zonificacion(
            \$m->id, 
            \$m->codigo, 
            \$m->nombre, 
            \$m->esta_activo, 
            new DateTimeImmutable(\$m->fecha_digitacion->format('Y-m-d H:i:s.uP')),
            \$m->usuario_id
        );
    }
}
");

// 7. Provider
file_put_contents("$base/Infrastructure/Providers/ZonificacionServiceProvider.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Infrastructure\Persistence\Repositories\EloquentZonificacionRepository;

class ZonificacionServiceProvider extends ServiceProvider {
    public function register(): void {
        \$this->app->bind(ZonificacionRepositoryInterface::class, EloquentZonificacionRepository::class);
    }
    public function boot(): void {
        \$this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
");

// 8. Controller
file_put_contents("$base/Presentation/Controllers/ZonificacionController.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Presentation\Controllers;

use App\Modules\Zonificacion\Application\UseCases\ListarZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\ObtenerZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\CrearZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\ActualizarZonificacionUseCase;
use App\Modules\Zonificacion\Application\UseCases\EliminarZonificacionUseCase;
use App\Modules\Zonificacion\Application\DTOs\GuardarZonificacionDTO;
use App\Modules\Zonificacion\Presentation\Requests\GuardarZonificacionRequest;
use App\Modules\Zonificacion\Presentation\Resources\ZonificacionResource;
use Illuminate\Http\JsonResponse;

class ZonificacionController {
    public function __construct(
        private readonly ListarZonificacionUseCase \$listar,
        private readonly ObtenerZonificacionUseCase \$obtener,
        private readonly CrearZonificacionUseCase \$crear,
        private readonly ActualizarZonificacionUseCase \$actualizar,
        private readonly EliminarZonificacionUseCase \$eliminar
    ) {}

    public function index(): JsonResponse {
        return new JsonResponse(ZonificacionResource::collection(\$this->listar->execute()));
    }
    public function show(int \$id): JsonResponse {
        return new JsonResponse(new ZonificacionResource(\$this->obtener->execute(\$id)));
    }
    public function store(GuardarZonificacionRequest \$request): JsonResponse {
        \$dto = new GuardarZonificacionDTO(
            codigo: \$request->codigo, 
            nombre: \$request->nombre, 
            estaActivo: (bool)\$request->esta_activo,
            usuarioId: \$request->user('api')?->id ?? 1
        );
        \$entity = \$this->crear->execute(\$dto);
        return new JsonResponse(new ZonificacionResource(\$entity), 201);
    }
    public function update(GuardarZonificacionRequest \$request, int \$id): JsonResponse {
        \$dto = new GuardarZonificacionDTO(
            codigo: \$request->codigo, 
            nombre: \$request->nombre, 
            estaActivo: (bool)\$request->esta_activo,
            usuarioId: \$request->user('api')?->id ?? 1
        );
        \$entity = \$this->actualizar->execute(\$id, \$dto);
        return new JsonResponse(new ZonificacionResource(\$entity));
    }
    public function destroy(int \$id): JsonResponse {
        \$this->eliminar->execute(\$id);
        return new JsonResponse(null, 204);
    }
}
");

// 9. Request
file_put_contents("$base/Presentation/Requests/GuardarZonificacionRequest.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarZonificacionRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'codigo' => 'required|string|max:30',
            'nombre' => 'required|string|max:150',
            'esta_activo' => 'required|boolean'
        ];
    }
}
");

// 10. Resource
file_put_contents("$base/Presentation/Resources/ZonificacionResource.php", "<?php
declare(strict_types=1);
namespace App\Modules\Zonificacion\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Modules\Zonificacion\Domain\Entities\Zonificacion \$resource
 */
class ZonificacionResource extends JsonResource {
    /** @return array<string, mixed> */
    public function toArray(Request \$request): array {
        return [
            'id' => \$this->resource->id,
            'codigo' => \$this->resource->codigo,
            'nombre' => \$this->resource->nombre,
            'esta_activo' => \$this->resource->estaActivo,
            'fecha_digitacion' => \$this->resource->fechaDigitacion->format('Y-m-d\TH:i:s.uP'),
            'usuario' => \$this->resource->usuarioId,
        ];
    }
}
");

// 11. Routes
file_put_contents("$base/Presentation/Routes/api.php", "<?php
declare(strict_types=1);
use Illuminate\Support\Facades\Route;
use App\Modules\Zonificacion\Presentation\Controllers\ZonificacionController;

Route::prefix('api/lf-itse')->middleware('api')->group(function () {
    Route::apiResource('zonificaciones', ZonificacionController::class);
});
");

echo "Zonificacion generated!\n";
