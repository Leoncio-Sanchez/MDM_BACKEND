<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Application;

use App\Modules\Seguridad\Application\DTOs\LoginDTO;
use App\Modules\Seguridad\Application\UseCases\AutenticarUsuarioUseCase;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;
use Mockery;

it('autentica usuario con credenciales válidas y devuelve TokenPair', function () {
    $user = new AuthenticatedUser(
        id: 1,
        username: 'admin',
        email: 'admin@test.com',
        role: 'ADMIN_MUNICIPALIDAD',
        ruc: null,
        passwordHash: 'pbkdf2_sha256$...',
    );

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('buscarPorUsername')->once()->with('admin')->andReturn($user);

    $hasher = Mockery::mock(PasswordHasherInterface::class);
    $hasher->shouldReceive('verify')->once()->with('admin', $user->passwordHash)->andReturn(true);

    $tokens = Mockery::mock(TokenServiceInterface::class);
    $tokens->shouldReceive('issue')->once()->with($user)->andReturn(
        new TokenPair(access: 'aaa.bbb.ccc', refresh: 'ddd.eee.fff'),
    );

    $useCase = new AutenticarUsuarioUseCase($users, $hasher, $tokens);
    $result = $useCase->execute(new LoginDTO('admin', 'admin'));

    expect($result->access)->toBe('aaa.bbb.ccc')
        ->and($result->refresh)->toBe('ddd.eee.fff');
});

it('lanza CredencialesInvalidas si el usuario no existe', function () {
    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('buscarPorUsername')->once()->with('nadie')->andReturn(null);

    $useCase = new AutenticarUsuarioUseCase(
        $users,
        Mockery::mock(PasswordHasherInterface::class),
        Mockery::mock(TokenServiceInterface::class),
    );

    expect(fn () => $useCase->execute(new LoginDTO('nadie', 'x')))
        ->toThrow(CredencialesInvalidas::class);
});

it('lanza CredencialesInvalidas si la password es incorrecta', function () {
    $user = new AuthenticatedUser(1, 'admin', 'a@t.com', 'ADMIN_MUNICIPALIDAD', null, 'hash');

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('buscarPorUsername')->once()->with('admin')->andReturn($user);

    $hasher = Mockery::mock(PasswordHasherInterface::class);
    $hasher->shouldReceive('verify')->once()->with('wrong', 'hash')->andReturn(false);

    $useCase = new AutenticarUsuarioUseCase(
        $users,
        $hasher,
        Mockery::mock(TokenServiceInterface::class),
    );

    expect(fn () => $useCase->execute(new LoginDTO('admin', 'wrong')))
        ->toThrow(CredencialesInvalidas::class);
});

it('mensaje exacto de Django en CredencialesInvalidas', function () {
    $e = CredencialesInvalidas::default();
    expect($e->getMessage())->toBe('La combinación de credenciales no tiene una cuenta activa');
});

it('LoginDTO construye correctamente desde array', function () {
    $dto = LoginDTO::fromArray(['username' => 'testuser', 'password' => 'secret123']);
    expect($dto->username)->toBe('testuser')
        ->and($dto->password)->toBe('secret123');
});
