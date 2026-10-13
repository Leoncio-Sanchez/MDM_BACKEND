<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Tests\Feature;

use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models\UnidadOrganicaModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UnidadOrganicaApiTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        UsuarioModel::truncate();
        $admin = UsuarioModel::create(\Tests\Fixtures\UsuarioAuth::datos([
            'id' => 1,
            'username' => 'admin',
            'password' => 'somehash',
            'is_superuser' => true,
            'is_staff' => true,
            'is_active' => true,
        ]));

        /** @var TokenServiceInterface $tokenService */
        $tokenService = app(TokenServiceInterface::class);
        $authUser = new AuthenticatedUser(
            id: (int) $admin->id, // el id real (la secuencia no siempre arranca en 1)
            username: 'admin',
            email: 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'somehash'
        );
        $this->token = $tokenService->issue($authUser)->access;
    }

    public function test_rechaza_peticion_sin_autenticacion(): void
    {
        $response = $this->getJson('/api/lf-itse/unidades-organicas');
        $response->assertStatus(401);
    }

    public function test_lista_unidades_organicas(): void
    {
        UnidadOrganicaModel::truncate();

        UnidadOrganicaModel::create([
            'id' => 1,
            'codigo' => 'SGDEL',
            'nombre' => 'Sub Gerencia de Desarrollo Económico Local',
            'sigla' => 'SGDEL',
            'esta_activo' => true,
        ]);

        UnidadOrganicaModel::create([
            'id' => 2,
            'codigo' => 'SGRD',
            'nombre' => 'Sub Gerencia de Gestión de Riesgos de Desastres',
            'sigla' => 'SGRD',
            'esta_activo' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
        $this->assertSame('SGDEL', $data[0]['codigo']);
        $this->assertSame('Sub Gerencia de Desarrollo Económico Local', $data[0]['nombre']);
        $this->assertTrue($data[0]['esta_activo']);
    }

    public function test_lista_unidades_organicas_con_trailing_slash(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/unidades-organicas/');

        $response->assertStatus(200);
    }
}
