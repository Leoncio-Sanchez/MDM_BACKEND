<?php

declare(strict_types=1);

use App\Modules\Seguridad\Presentation\Console\CrearUsuariosPruebaCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('crea un usuario por perfil, con sus accesos, y vuelve a correr sin duplicar', function () {
    $this->artisan('usuarios:prueba')->assertSuccessful();
    $this->artisan('usuarios:prueba')->assertSuccessful();

    expect(DB::table('auth_user')->where('username', 'like', 'prueba\_%')->count())->toBe(count(CrearUsuariosPruebaCommand::PERFILES));

    $perfil = fn (string $usuario) => DB::table('usuarios_perfiles as up')
        ->join('auth_user as u', 'u.id', '=', 'up.user_id')
        ->where('u.username', $usuario)
        ->first(['u.is_staff', 'up.itse', 'up.es_jefe_itse', 'up.es_inspector', 'up.expedientes']);

    expect($perfil('prueba_jefe_itse'))->toMatchArray(['is_staff' => true, 'itse' => true, 'es_jefe_itse' => true, 'expedientes' => false])
        ->and($perfil('prueba_inspector1'))->toMatchArray(['is_staff' => false, 'es_inspector' => true, 'itse' => false]);
});

it('los usuarios creados inician sesión con la contraseña mostrada', function () {
    $archivo = tempnam(sys_get_temp_dir(), 'cred');
    $this->artisan('usuarios:prueba', ['--archivo' => $archivo])->assertSuccessful();
    preg_match('/Contraseña para todos: `([^`]+)`/', (string) file_get_contents($archivo), $m);
    unlink($archivo);

    $this->postJson('/api/auth/jwt/create/', ['username' => 'prueba_mesa', 'password' => $m[1]])
        ->assertOk()
        ->assertJsonStructure(['access', 'refresh']);
});

it('al eliminar, borra a los que no registraron nada y desactiva a los que sí', function () {
    $this->artisan('usuarios:prueba')->assertSuccessful();
    $mesa = DB::table('auth_user')->where('username', 'prueba_mesa')->value('id');
    // Mesa de partes ya asignó una inspección: hay una referencia a su usuario
    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => true]));
    $persona = DB::table('personas')->insertGetId(Registros::persona());
    $expediente = DB::table('expedientes')->insertGetId(Registros::expediente(['solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa]));
    DB::table('inspecciones_asignaciones')->insert([
        'expediente_id' => $expediente, 'inspector_id' => $mesa, 'nombre_comercial' => 'BODEGA', 'direccion' => 'JR. LIMA 1', 'asignado_por' => $mesa,
    ]);

    $this->artisan('usuarios:prueba', ['--eliminar' => true])->assertSuccessful();

    expect(DB::table('auth_user')->where('username', 'like', 'prueba\_%')->pluck('username')->all())->toBe(['prueba_mesa'])
        ->and(DB::table('auth_user')->where('id', $mesa)->value('is_active'))->toBeFalse();
});

it('crea el primer administrador con la contraseña escrita, sin mostrarla', function () {
    $this->artisan('usuarios:administrador', ['usuario' => 'jefe_ti', '--nombre' => 'ana ruiz'])
        ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'Clave-segura-2026')
        ->expectsQuestion('Repita la contraseña', 'Clave-segura-2026')
        ->assertSuccessful();

    $this->postJson('/api/auth/jwt/create/', ['username' => 'jefe_ti', 'password' => 'Clave-segura-2026'])->assertOk();
    expect(DB::table('auth_user')->where('username', 'jefe_ti')->value('first_name'))->toBe('ANA RUIZ');

    $this->artisan('usuarios:administrador', ['usuario' => 'otro'])
        ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'corta')
        ->assertFailed();
    $this->artisan('usuarios:administrador', ['usuario' => 'jefe_ti'])->assertFailed();
});

it('no corre en producción', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('usuarios:prueba')->assertFailed();
    expect(DB::table('auth_user')->where('username', 'like', 'prueba\_%')->count())->toBe(0);
});
