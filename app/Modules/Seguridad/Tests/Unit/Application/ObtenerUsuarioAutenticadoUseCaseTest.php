<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Application;

use App\Modules\Seguridad\Application\DTOs\ObtenerUsuarioDTO;
use App\Modules\Seguridad\Application\UseCases\ObtenerUsuarioAutenticadoUseCase;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Domain\Exceptions\UsuarioNoEncontrado;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use Mockery;

it('retorna AuthenticatedUser si el usuario existe por ID', function () {
    $user = new AuthenticatedUser(
        id: 5,
        username: 'inspector_test',
        email: 'inspector@muni.gob.pe',
        role: 'CIUDADANO',
        ruc: '20123456789',
        passwordHash: 'pbkdf2_sha256$...',
    );

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('buscarPorId')
        ->once()
        ->with(5)
        ->andReturn($user);

    $useCase = new ObtenerUsuarioAutenticadoUseCase($users);
    $result = $useCase->execute(new ObtenerUsuarioDTO(5));

    expect($result->id)->toBe(5)
        ->and($result->username)->toBe('inspector_test')
        ->and($result->email)->toBe('inspector@muni.gob.pe')
        ->and($result->role)->toBe('CIUDADANO')
        ->and($result->ruc)->toBe('20123456789');
});

it('lanza UsuarioNoEncontrado si el usuario no existe por ID', function () {
    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('buscarPorId')
        ->once()
        ->with(999)
        ->andReturn(null);

    $useCase = new ObtenerUsuarioAutenticadoUseCase($users);

    expect(fn () => $useCase->execute(new ObtenerUsuarioDTO(999)))
        ->toThrow(UsuarioNoEncontrado::class);
});
