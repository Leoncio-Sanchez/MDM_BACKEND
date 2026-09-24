<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Tests\Unit;

use App\Modules\Usuarios\Application\UseCases\ConstruirMenuUsuarioUseCase;
use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ConstruirMenuUsuarioTest extends TestCase
{
    public function test_usuario_sin_permisos_ve_solo_dashboard_y_reportes(): void
    {
        $repo = $this->createMock(UsuarioRepositoryInterface::class);
        $repo->method('obtenerOpcionesUsuario')->willReturn([
            'expedientes' => false,
            'licencias' => false,
            'itse' => false,
            'admin' => false,
        ]);

        $useCase = new ConstruirMenuUsuarioUseCase($repo);
        $menus = $useCase->execute(1);

        $ids = array_column($menus, 'id');
        $this->assertSame(['dashboard', 'reportes'], $ids);
    }

    public function test_usuario_admin_ve_menu_usuarios_y_catalogos(): void
    {
        $repo = $this->createMock(UsuarioRepositoryInterface::class);
        $repo->method('obtenerOpcionesUsuario')->willReturn([
            'expedientes' => true,
            'licencias' => true,
            'itse' => true,
            'admin' => true,
        ]);

        $useCase = new ConstruirMenuUsuarioUseCase($repo);
        $menus = $useCase->execute(1);

        $ids = array_column($menus, 'id');
        $this->assertContains('dashboard', $ids);
        $this->assertContains('expedientes', $ids);
        $this->assertContains('licencias-funcionamiento', $ids);
        $this->assertContains('certificados-itse', $ids);
        $this->assertContains('reportes', $ids);
        $this->assertContains('catalogos', $ids);
        $this->assertContains('usuarios', $ids);
    }
}
