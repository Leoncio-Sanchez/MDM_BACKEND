<?php

declare(strict_types=1);

use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentPersonaReadRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentTipoProcedimientoTupaReadRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentUsuarioReadRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('EloquentTipoProcedimientoTupaReadRepository busca por id y verifica existencia', function () {
    $repo = new EloquentTipoProcedimientoTupaReadRepository;

    expect($repo->buscarPorId(1))->toBeNull()
        ->and($repo->existe(1))->toBeFalse();

    DB::table('unidades_organicas')->insert([
        'id' => 1,
        'codigo' => 'UO-01',
        'nombre' => 'Unidad Organica 1',
        'esta_activo' => true,
    ]);

    DB::table('auth_user')->insert([
        'id' => 1,
        'username' => 'admin_u',
        'password' => 'secret',
        'is_active' => true,
        'is_staff' => false,
        'is_superuser' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tipos_procedimiento_tupa')->insert([
        'id' => 1,
        'codigo' => 'TUPA-01',
        'nombre' => 'Procedimiento 1',
        'plazo_atencion_dias' => 15,
        'dias_alerta_vencimiento' => 3,
        'esta_activo' => true,
        'requiere_lf' => true,
        'requiere_itse' => false,
        'fecha_digitacion' => now(),
        'usuario_id' => 1,
        'unidad_organica_id' => 1,
    ]);

    $tipo = $repo->buscarPorId(1);
    expect($tipo)->not->toBeNull()
        ->and($tipo->id)->toBe(1)
        ->and($tipo->plazo_atencion_dias)->toBe(15)
        ->and($repo->existe(1))->toBeTrue()
        ->and($repo->existe(999))->toBeFalse();
});

it('EloquentPersonaReadRepository busca por id y verifica existencia', function () {
    $repo = new EloquentPersonaReadRepository;

    expect($repo->buscarPorId(10))->toBeNull()
        ->and($repo->existe(10))->toBeFalse();

    DB::table('personas')->insert([
        'id' => 10,
        'nombres' => 'Juan Perez',
        'tipo_persona' => 'N',
        'sexo' => 'M',
        'user_id' => 1,
    ]);

    $persona = $repo->buscarPorId(10);
    expect($persona)->not->toBeNull()
        ->and($persona->id)->toBe(10)
        ->and($persona->nombres)->toBe('Juan Perez')
        ->and($repo->existe(10))->toBeTrue()
        ->and($repo->existe(999))->toBeFalse();
});

it('EloquentUsuarioReadRepository busca por id y verifica existencia', function () {
    $repo = new EloquentUsuarioReadRepository;

    expect($repo->buscarPorId(5))->toBeNull()
        ->and($repo->existe(5))->toBeFalse();

    DB::table('auth_user')->insert([
        'id' => 5,
        'username' => 'testuser',
        'password' => 'secret',
        'is_active' => true,
        'is_staff' => false,
        'is_superuser' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = $repo->buscarPorId(5);
    expect($user)->not->toBeNull()
        ->and($user->id)->toBe(5)
        ->and($user->username)->toBe('testuser')
        ->and($repo->existe(5))->toBeTrue()
        ->and($repo->existe(999))->toBeFalse();
});
