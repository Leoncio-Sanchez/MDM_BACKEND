<?php
$base = __DIR__ . '/app/Modules/NivelRiesgo';
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

// 1. Domain Entity
file_put_contents("$base/Domain/Entities/NivelRiesgo.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Domain\Entities;

class NivelRiesgo {
    public function __construct(
        public readonly ?int \$id,
        public readonly string \$codigo,
        public readonly string \$nombre,
        public readonly bool \$estaActivo
    ) {}
}
");

// 2. Exception
file_put_contents("$base/Domain/Exceptions/NivelRiesgoNoEncontrado.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Domain\Exceptions;

class NivelRiesgoNoEncontrado extends \DomainException {
    public function __construct() {
        parent::__construct('Nivel de Riesgo no encontrado');
    }
}
");

// 3. Contract
file_put_contents("$base/Domain/Contracts/NivelRiesgoRepositoryInterface.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Domain\Contracts;

use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;

interface NivelRiesgoRepositoryInterface {
    public function obtenerTodos(): array;
    public function buscarPorId(int \$id): ?NivelRiesgo;
    public function guardar(NivelRiesgo \$nivelRiesgo): NivelRiesgo;
    public function actualizar(NivelRiesgo \$nivelRiesgo): NivelRiesgo;
    public function eliminar(int \$id): void;
}
");

// 4. UseCases
file_put_contents("$base/Application/UseCases/ListarNivelRiesgoUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;

final readonly class ListarNivelRiesgoUseCase {
    public function __construct(private NivelRiesgoRepositoryInterface \$repository) {}
    public function execute(): array {
        return \$this->repository->obtenerTodos();
    }
}
");

file_put_contents("$base/Application/UseCases/ObtenerNivelRiesgoUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Exceptions\NivelRiesgoNoEncontrado;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;

final readonly class ObtenerNivelRiesgoUseCase {
    public function __construct(private NivelRiesgoRepositoryInterface \$repository) {}
    public function execute(int \$id): NivelRiesgo {
        \$entity = \$this->repository->buscarPorId(\$id);
        if (!\$entity) throw new NivelRiesgoNoEncontrado();
        return \$entity;
    }
}
");

file_put_contents("$base/Application/DTOs/GuardarNivelRiesgoDTO.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\DTOs;

final readonly class GuardarNivelRiesgoDTO {
    public function __construct(
        public string \$codigo,
        public string \$nombre,
        public bool \$esta_activo
    ) {}
}
");

file_put_contents("$base/Application/UseCases/CrearNivelRiesgoUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;

final readonly class CrearNivelRiesgoUseCase {
    public function __construct(private NivelRiesgoRepositoryInterface \$repository) {}
    public function execute(GuardarNivelRiesgoDTO \$dto): NivelRiesgo {
        \$entity = new NivelRiesgo(null, \$dto->codigo, \$dto->nombre, \$dto->esta_activo);
        return \$this->repository->guardar(\$entity);
    }
}
");

file_put_contents("$base/Application/UseCases/ActualizarNivelRiesgoUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Domain\Exceptions\NivelRiesgoNoEncontrado;
use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;

final readonly class ActualizarNivelRiesgoUseCase {
    public function __construct(private NivelRiesgoRepositoryInterface \$repository) {}
    public function execute(int \$id, GuardarNivelRiesgoDTO \$dto): NivelRiesgo {
        \$entity = \$this->repository->buscarPorId(\$id);
        if (!\$entity) throw new NivelRiesgoNoEncontrado();
        \$updated = new NivelRiesgo(\$id, \$dto->codigo, \$dto->nombre, \$dto->esta_activo);
        return \$this->repository->actualizar(\$updated);
    }
}
");

file_put_contents("$base/Application/UseCases/EliminarNivelRiesgoUseCase.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Application\UseCases;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;

final readonly class EliminarNivelRiesgoUseCase {
    public function __construct(private NivelRiesgoRepositoryInterface \$repository) {}
    public function execute(int \$id): void {
        \$this->repository->eliminar(\$id);
    }
}
");

// 5. Infra Models
file_put_contents("$base/Infrastructure/Persistence/Models/NivelRiesgoModel.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class NivelRiesgoModel extends Model {
    protected \$table = 'niveles_riesgo';
    public \$timestamps = false;
    protected \$fillable = ['codigo', 'nombre', 'esta_activo'];
    protected \$casts = ['esta_activo' => 'boolean'];
}
");

// 6. Repo
file_put_contents("$base/Infrastructure/Persistence/Repositories/EloquentNivelRiesgoRepository.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Infrastructure\Persistence\Repositories;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Infrastructure\Persistence\Models\NivelRiesgoModel;

class EloquentNivelRiesgoRepository implements NivelRiesgoRepositoryInterface {
    public function obtenerTodos(): array {
        return NivelRiesgoModel::all()->map(fn(\$m) => \$this->toDomain(\$m))->toArray();
    }
    public function buscarPorId(int \$id): ?NivelRiesgo {
        \$m = NivelRiesgoModel::find(\$id);
        return \$m ? \$this->toDomain(\$m) : null;
    }
    public function guardar(NivelRiesgo \$entity): NivelRiesgo {
        \$m = NivelRiesgoModel::create([
            'codigo' => \$entity->codigo,
            'nombre' => \$entity->nombre,
            'esta_activo' => \$entity->estaActivo
        ]);
        return \$this->toDomain(\$m);
    }
    public function actualizar(NivelRiesgo \$entity): NivelRiesgo {
        \$m = NivelRiesgoModel::findOrFail(\$entity->id);
        \$m->update([
            'codigo' => \$entity->codigo,
            'nombre' => \$entity->nombre,
            'esta_activo' => \$entity->estaActivo
        ]);
        return \$this->toDomain(\$m);
    }
    public function eliminar(int \$id): void {
        NivelRiesgoModel::destroy(\$id);
    }
    private function toDomain(NivelRiesgoModel \$m): NivelRiesgo {
        return new NivelRiesgo(\$m->id, \$m->codigo, \$m->nombre, \$m->esta_activo);
    }
}
");

// 7. Provider
file_put_contents("$base/Infrastructure/Providers/NivelRiesgoServiceProvider.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Infrastructure\Persistence\Repositories\EloquentNivelRiesgoRepository;

class NivelRiesgoServiceProvider extends ServiceProvider {
    public function register(): void {
        \$this->app->bind(NivelRiesgoRepositoryInterface::class, EloquentNivelRiesgoRepository::class);
    }
}
");

// 8. Controller
file_put_contents("$base/Presentation/Controllers/NivelRiesgoController.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Presentation\Controllers;

use App\Modules\NivelRiesgo\Application\UseCases\ListarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\ObtenerNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\CrearNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\ActualizarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\UseCases\EliminarNivelRiesgoUseCase;
use App\Modules\NivelRiesgo\Application\DTOs\GuardarNivelRiesgoDTO;
use App\Modules\NivelRiesgo\Presentation\Requests\GuardarNivelRiesgoRequest;
use App\Modules\NivelRiesgo\Presentation\Resources\NivelRiesgoResource;
use Illuminate\Http\JsonResponse;

class NivelRiesgoController {
    public function __construct(
        private readonly ListarNivelRiesgoUseCase \$listar,
        private readonly ObtenerNivelRiesgoUseCase \$obtener,
        private readonly CrearNivelRiesgoUseCase \$crear,
        private readonly ActualizarNivelRiesgoUseCase \$actualizar,
        private readonly EliminarNivelRiesgoUseCase \$eliminar
    ) {}

    public function index(): JsonResponse {
        return new JsonResponse(NivelRiesgoResource::collection(\$this->listar->execute()));
    }
    public function show(int \$id): JsonResponse {
        return new JsonResponse(new NivelRiesgoResource(\$this->obtener->execute(\$id)));
    }
    public function store(GuardarNivelRiesgoRequest \$request): JsonResponse {
        \$dto = new GuardarNivelRiesgoDTO(\$request->codigo, \$request->nombre, \$request->esta_activo);
        \$entity = \$this->crear->execute(\$dto);
        return new JsonResponse(new NivelRiesgoResource(\$entity), 201);
    }
    public function update(GuardarNivelRiesgoRequest \$request, int \$id): JsonResponse {
        \$dto = new GuardarNivelRiesgoDTO(\$request->codigo, \$request->nombre, \$request->esta_activo);
        \$entity = \$this->actualizar->execute(\$id, \$dto);
        return new JsonResponse(new NivelRiesgoResource(\$entity));
    }
    public function destroy(int \$id): JsonResponse {
        \$this->eliminar->execute(\$id);
        return new JsonResponse(null, 204);
    }
}
");

// 9. Request
file_put_contents("$base/Presentation/Requests/GuardarNivelRiesgoRequest.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarNivelRiesgoRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'codigo' => 'required|string|max:10',
            'nombre' => 'required|string|max:50',
            'esta_activo' => 'required|boolean'
        ];
    }
}
");

// 10. Resource
file_put_contents("$base/Presentation/Resources/NivelRiesgoResource.php", "<?php
declare(strict_types=1);
namespace App\Modules\NivelRiesgo\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NivelRiesgoResource extends JsonResource {
    public function toArray(Request \$request): array {
        return [
            'id' => \$this->id,
            'codigo' => \$this->codigo,
            'nombre' => \$this->nombre,
            'esta_activo' => \$this->estaActivo,
        ];
    }
}
");

echo "NivelRiesgo skeleton generated!\n";
