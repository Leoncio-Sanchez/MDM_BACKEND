<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Domain;

use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use App\Modules\Seguridad\Domain\Exceptions\SeguridadError;
use App\Modules\Seguridad\Domain\Exceptions\TokenInvalido;
use App\Modules\Seguridad\Domain\Exceptions\UsuarioNoEncontrado;
use App\Modules\Seguridad\Domain\ValueObjects\AccessToken;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Domain\ValueObjects\Credenciales;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DomainSeguridadTest extends TestCase
{
    public function test_credenciales_se_construye_correctamente(): void
    {
        $credenciales = new Credenciales('admin', 'secret123');

        $this->assertSame('admin', $credenciales->username);
        $this->assertSame('secret123', $credenciales->password);
    }

    public function test_token_pair_to_array_devuelve_las_dos_keys(): void
    {
        $tokenPair = new TokenPair('access-token-123', 'refresh-token-456');

        $array = $tokenPair->toArray();

        $this->assertSame('access-token-123', $array['access']);
        $this->assertSame('refresh-token-456', $array['refresh']);
        $this->assertCount(2, $array);
    }

    public function test_authenticated_user_acepta_ruc_null(): void
    {
        $user = new AuthenticatedUser(
            id: 1,
            username: 'admin',
            email: 'admin@correo.com',
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: 'pbkdf2_sha256$1200000$salt$hash'
        );

        $this->assertSame(1, $user->id);
        $this->assertSame('admin', $user->username);
        $this->assertSame('admin@correo.com', $user->email);
        $this->assertSame('ADMIN_MUNICIPALIDAD', $user->role);
        $this->assertNull($user->ruc);
        $this->assertSame('pbkdf2_sha256$1200000$salt$hash', $user->passwordHash);
    }

    public function test_credenciales_invalidas_default_tiene_mensaje_exacto_de_django(): void
    {
        $exception = CredencialesInvalidas::default();

        $this->assertSame(
            'La combinación de credenciales no tiene una cuenta activa',
            $exception->getMessage()
        );
        $this->assertInstanceOf(SeguridadError::class, $exception);
    }

    public function test_token_invalido_default_tiene_mensaje_generico(): void
    {
        $exception = TokenInvalido::default();

        $this->assertSame('Token inválido o expirado.', $exception->getMessage());
        $this->assertInstanceOf(SeguridadError::class, $exception);
    }

    public function test_usuario_no_encontrado_por_username_incluye_el_username(): void
    {
        $exception = UsuarioNoEncontrado::porUsername('jperez');

        $this->assertSame("Usuario 'jperez' no encontrado.", $exception->getMessage());
        $this->assertInstanceOf(SeguridadError::class, $exception);

        $exceptionPorId = UsuarioNoEncontrado::porId(42);
        $this->assertSame('Usuario con ID 42 no encontrado.', $exceptionPorId->getMessage());
    }

    public function test_seguridad_error_es_abstract(): void
    {
        $reflection = new ReflectionClass(SeguridadError::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function test_los_value_objects_son_readonly(): void
    {
        $classes = [
            Credenciales::class,
            TokenPair::class,
            AccessToken::class,
            AuthenticatedUser::class,
        ];

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isReadOnly(),
                "La clase {$class} debe ser readonly"
            );
        }
    }
}
