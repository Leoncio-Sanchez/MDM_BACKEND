<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Usuario;
use App\Models\UsuarioPerfil;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Services\UsuarioService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UsuarioServiceTest extends TestCase
{
    private UsuarioService $service;

    private PasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('first_name')->default('');
                $table->string('last_name')->default('');
                $table->string('email')->default('');
                $table->boolean('is_superuser')->default(false);
                $table->boolean('is_staff')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login')->nullable();
                $table->timestamp('date_joined')->nullable();
            });
        }

        if (! Schema::hasTable('usuarios_perfiles')) {
            Schema::create('usuarios_perfiles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->boolean('expedientes')->default(false);
                $table->boolean('licencias')->default(false);
                $table->boolean('itse')->default(false);
                $table->boolean('admin')->default(false);
                $table->integer('user_id_digitador')->default(1);
                $table->timestamp('fecha_digitacion')->nullable();
            });
        }

        UsuarioPerfil::truncate();
        Usuario::truncate();

        $this->hasher = app(PasswordHasherInterface::class);
        $this->service = new UsuarioService($this->hasher);
    }

    public function test_usuario_sin_permisos_ve_solo_dashboard_y_reportes(): void
    {
        $user = Usuario::create([
            'id' => 10,
            'username' => 'operador',
            'password' => 'secret',
            'is_active' => true,
        ]);

        $menus = $this->service->construirMenus($user->id);
        $ids = array_column($menus, 'id');

        $this->assertSame(['dashboard', 'reportes'], $ids);
    }

    public function test_usuario_admin_ve_menu_usuarios_y_catalogos(): void
    {
        $user = Usuario::create([
            'id' => 1,
            'username' => 'admin',
            'password' => 'secret',
            'is_superuser' => true,
            'is_active' => true,
        ]);

        $menus = $this->service->construirMenus($user->id);
        $ids = array_column($menus, 'id');

        $this->assertContains('dashboard', $ids);
        $this->assertContains('expedientes', $ids);
        $this->assertContains('licencias-funcionamiento', $ids);
        $this->assertContains('certificados-itse', $ids);
        $this->assertContains('reportes', $ids);
        $this->assertContains('catalogos', $ids);
        $this->assertContains('usuarios', $ids);
    }

    public function test_cambio_password_con_pbkdf2_exitoso(): void
    {
        $user = Usuario::create([
            'username' => 'cambio_pass',
            'password' => 'old_pass',
            'is_active' => true,
        ]);

        $result = $this->service->cambiarPassword($user->id, 'nueva_clave_segura', 'nueva_clave_segura');

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertSame('Contraseña actualizada correctamente.', $result['data']['mensaje']);

        $user->refresh();
        $this->assertStringStartsWith('pbkdf2_sha256$', $user->password);
    }

    public function test_cambio_password_rechaza_vacio_no_coincidente_o_corto(): void
    {
        $user = Usuario::create([
            'username' => 'valida_pass',
            'password' => 'pass',
            'is_active' => true,
        ]);

        $vacio = $this->service->cambiarPassword($user->id, '', '');
        $this->assertSame(400, $vacio['status']);
        $this->assertSame('La contraseña no puede estar vacía.', $vacio['data']['error']);

        $noCoincide = $this->service->cambiarPassword($user->id, '1234567', '7654321');
        $this->assertSame(400, $noCoincide['status']);
        $this->assertSame('Las contraseñas no coinciden.', $noCoincide['data']['error']);

        $corto = $this->service->cambiarPassword($user->id, '12345', '12345');
        $this->assertSame(400, $corto['status']);
        $this->assertSame('La contraseña debe tener al menos 6 caracteres.', $corto['data']['error']);
    }

    public function test_nombre_completo_fallback_a_username_cuando_first_last_vacios(): void
    {
        $u1 = new Usuario(['username' => 'testuser', 'first_name' => '', 'last_name' => '']);
        $this->assertSame('testuser', $u1->nombre_completo);

        $u2 = new Usuario(['username' => 'testuser', 'first_name' => 'Juan', 'last_name' => 'Perez']);
        $this->assertSame('Juan Perez', $u2->nombre_completo);
    }

    public function test_fechas_se_formatean_en_america_lima(): void
    {
        $carbon = Carbon::parse('2026-04-24T18:48:01.415596Z');
        $formateada = $carbon->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');

        $this->assertSame('2026-04-24T13:48:01.415596-05:00', $formateada);
    }
}
