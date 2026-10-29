<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Tests\Feature;

use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioPerfilModel;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InspectorApiTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        UsuarioModel::truncate();

        UsuarioModel::create(\Tests\Fixtures\UsuarioAuth::datos([
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
            id: 1,
            username: 'admin',
            email: 'admin@muni.gob.pe',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'somehash'
        );
        $this->token = $tokenService->issue($authUser)->access;
    }

    /**
     * Inspector = usuario con la casilla es_inspector en su perfil.
     *
     * @param  array<string, mixed>  $datos
     */
    private function inspector(array $datos): UsuarioModel
    {
        $usuario = UsuarioModel::create($datos);
        UsuarioPerfilModel::create([
            'user_id' => $usuario->id, 'expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
            'es_inspector' => true, 'es_jefe_itse' => false, 'reportes_completos' => false,
            'user_id_digitador' => 1, 'fecha_digitacion' => now(),
        ]);

        return $usuario;
    }

    public function test_listar_inspectores_devuelve_200(): void
    {
        $this->inspector(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector_carlos',
            'first_name' => 'Carlos',
            'last_name' => 'Sanchez',
            'is_active' => true,
            'password' => 'somehash',
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertGreaterThanOrEqual(1, count($response->json()));
    }

    public function test_crear_inspector_devuelve_201(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/lf-itse/inspectores/', [
                'username' => 'inspector_nuevo',
                'password' => 'clave1234',
                'first_name' => 'Roberto',
                'last_name' => 'Gomez',
                'email' => 'roberto@muni.gob.pe',
            ]);

        $response->assertStatus(201);
        $this->assertSame('inspector_nuevo', $response->json('username'));
        $this->assertSame('Roberto Gomez', $response->json('nombre_completo'));
    }

    public function test_obtener_inspector_por_id(): void
    {
        $insp = $this->inspector(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector_detalle',
            'first_name' => 'Pedro',
            'password' => 'somehash',
            'is_active' => true,
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/'.$insp->id.'/');

        $response->assertStatus(200);
        $this->assertSame('inspector_detalle', $response->json('username'));
    }

    public function test_buscar_inspectores(): void
    {
        $this->inspector(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector_busqueda',
            'first_name' => 'Mariana',
            'password' => 'somehash',
            'is_active' => true,
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/lf-itse/inspectores/buscar/?busqueda=Mariana');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertSame('inspector_busqueda', $response->json()[0]['username']);
    }

    public function test_actualizar_inspector(): void
    {
        $insp = $this->inspector(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector_mod',
            'first_name' => 'Luis',
            'password' => 'somehash',
            'is_active' => true,
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/lf-itse/inspectores/'.$insp->id.'/', [
                'first_name' => 'Luis Alberto',
                'last_name' => 'Ramirez',
            ]);

        $response->assertStatus(200);
        $this->assertSame('Luis Alberto Ramirez', $response->json('nombre_completo'));
    }

    /** Ya no borra la cuenta: la desactiva (los usuarios no se eliminan) */
    public function test_eliminar_inspector_lo_desactiva(): void
    {
        $insp = $this->inspector(\Tests\Fixtures\UsuarioAuth::datos([
            'username' => 'inspector_eliminar',
            'password' => 'somehash',
            'is_active' => true,
        ]));

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/lf-itse/inspectores/'.$insp->id.'/');

        $response->assertStatus(204);
        $this->assertFalse((bool) UsuarioModel::find($insp->id)?->is_active);
    }
}
