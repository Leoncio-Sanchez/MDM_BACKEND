<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Infrastructure;

use App\Modules\Seguridad\Infrastructure\Persistence\Models\UserAuthModel;
use App\Modules\Seguridad\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function ($table) {
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

        UserAuthModel::truncate();

        UserAuthModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'admin',
            'password' => 'pbkdf2_sha256$1200000$salt$hash',
            'email' => 'admin@muni.gob.pe',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]));

        UserAuthModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector1',
            'password' => 'pbkdf2_sha256$1200000$salt$hash2',
            'email' => 'inspector1@muni.gob.pe',
            'is_superuser' => false,
            'is_staff' => false,
            'is_active' => true,
        ]));
    }

    public function test_busca_usuario_por_username_existente(): void
    {
        $repo = new EloquentUserRepository;
        $user = $repo->buscarPorUsername('admin');

        $this->assertNotNull($user);
        $this->assertSame(1, $user->id);
        $this->assertSame('admin', $user->username);
        $this->assertSame('ADMIN_MUNICIPALIDAD', $user->role);
        $this->assertStringStartsWith('pbkdf2_sha256$', $user->passwordHash);
    }

    public function test_busca_usuario_por_id_existente(): void
    {
        $repo = new EloquentUserRepository;
        $user = $repo->buscarPorId(1);

        $this->assertNotNull($user);
        $this->assertSame(1, $user->id);
        $this->assertSame('admin', $user->username);
    }

    public function test_retorna_null_si_el_usuario_no_existe(): void
    {
        $repo = new EloquentUserRepository;

        $this->assertNull($repo->buscarPorUsername('no_existe_usuario_xyz'));
        $this->assertNull($repo->buscarPorId(999999));
    }

    public function test_asigna_role_ciudadano_para_usuario_sin_staff(): void
    {
        $repo = new EloquentUserRepository;
        $user = $repo->buscarPorUsername('inspector1');

        $this->assertNotNull($user);
        $this->assertSame('CIUDADANO', $user->role);
    }
}
