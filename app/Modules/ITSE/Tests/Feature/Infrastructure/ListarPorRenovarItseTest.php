<?php
declare(strict_types=1);

use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentItseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentItseRepository();
    $this->hoy = now()->startOfDay();

    $this->crear = fn (string $nombre, int $diasParaVencer, array $extra = []): int => DB::table('itse')->insertGetId(
        Registros::itse(array_merge([
            'nombre_comercial' => $nombre,
            'fecha_expedicion' => $this->hoy->copy()->subYears(2)->toDateString(),
            'fecha_caducidad' => $this->hoy->copy()->addDays($diasParaVencer)->toDateString(),
        ], $extra))
    );
    $this->nombres = fn (array $itses): array => array_map(fn ($i) => $i->nombreComercial, $itses);
    $this->listar = fn (int $dias = 30): array => $this->repo->listarPorRenovar(
        $this->hoy->toDateString(),
        $this->hoy->copy()->addDays($dias)->toDateString(),
    );
});

it('lista los ITSE que vencen en el rango, del más próximo al más lejano', function () {
    ($this->crear)('VENCE EN 20', 20);
    ($this->crear)('VENCE EN 5', 5);
    ($this->crear)('VENCE EN 90', 90);
    ($this->crear)('YA VENCIO', -3);

    expect(($this->nombres)(($this->listar)()))->toBe(['VENCE EN 5', 'VENCE EN 20']);
});

it('incluye certificados originales, no solo renovaciones', function () {
    ($this->crear)('ORIGINAL', 10, ['itse_principal_id' => null]);

    expect(($this->nombres)(($this->listar)()))->toBe(['ORIGINAL']);
});

it('excluye los ITSE inactivados', function () {
    $id = ($this->crear)('CLAUSURADO', 10);
    $estado = DB::table('estados')->insertGetId(Registros::estado(['nombre' => 'CLAUSURA', 'esta_activo' => false]));
    DB::table('itse_estados')->insert([
        'itse_id' => $id, 'estado_id' => $estado, 'fecha_estado' => now(), 'documento' => 'RES',
        'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);

    expect(($this->listar)())->toBe([]);
});

it('excluye los que ya tienen una renovación, pero lista la renovación cuando le toca vencer', function () {
    $original = ($this->crear)('ORIGINAL RENOVADO', 10);
    ($this->crear)('SU RENOVACION', 25, ['itse_principal_id' => $original]);

    expect(($this->nombres)(($this->listar)()))->toBe(['SU RENOVACION']);
});

it('vuelve a listar el original si su renovación fue inactivada', function () {
    $original = ($this->crear)('ORIGINAL', 10);
    $renovacion = ($this->crear)('RENOVACION ANULADA', 400, ['itse_principal_id' => $original]);
    $estado = DB::table('estados')->insertGetId(Registros::estado(['nombre' => 'ANULADO', 'esta_activo' => false]));
    DB::table('itse_estados')->insert([
        'itse_id' => $renovacion, 'estado_id' => $estado, 'fecha_estado' => now(), 'documento' => 'RES',
        'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1,
    ]);

    expect(($this->nombres)(($this->listar)()))->toBe(['ORIGINAL']);
});
