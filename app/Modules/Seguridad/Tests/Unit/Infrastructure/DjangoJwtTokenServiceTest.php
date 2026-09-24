<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Infrastructure;

use App\Modules\Seguridad\Domain\Exceptions\TokenInvalido;
use App\Modules\Seguridad\Domain\ValueObjects\AuthenticatedUser;
use App\Modules\Seguridad\Infrastructure\Jwt\DjangoJwtTokenService;

it('emite access y refresh con claims exactos y user_id como STRING', function () {
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    $user = new AuthenticatedUser(
        id: 1,
        username: 'admin',
        email: 'admin@correo.com',
        role: 'ADMIN_MUNICIPALIDAD',
        ruc: null,
        passwordHash: '',
    );

    $pair = $service->issue($user);

    expect($pair->access)->toBeString()->not->toBeEmpty()
        ->and($pair->refresh)->toBeString()->not->toBeEmpty();

    $accessPayload = json_decode(base64_decode(strtr(explode('.', $pair->access)[1], '-_', '+/'), true), true);
    expect($accessPayload['token_type'])->toBe('access')
        ->and($accessPayload['user_id'])->toBe('1')
        ->and($accessPayload['username'])->toBe('admin')
        ->and($accessPayload['role'])->toBe('ADMIN_MUNICIPALIDAD')
        ->and($accessPayload['ruc'])->toBeNull()
        ->and(strlen($accessPayload['jti']))->toBe(32)
        ->and($accessPayload['exp'])->toBeGreaterThan($accessPayload['iat']);

    $refreshPayload = json_decode(base64_decode(strtr(explode('.', $pair->refresh)[1], '-_', '+/'), true), true);
    expect($refreshPayload['token_type'])->toBe('refresh')
        ->and($refreshPayload['user_id'])->toBe('1')
        ->and($refreshPayload['username'])->toBe('admin')
        ->and($refreshPayload['role'])->toBe('ADMIN_MUNICIPALIDAD')
        ->and($refreshPayload['exp'])->toBeGreaterThan($accessPayload['exp']);
});

it('role asignado correctamente según AuthenticatedUser (CIUDADANO o ADMIN_MUNICIPALIDAD)', function () {
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    $ciudadano = new AuthenticatedUser(
        id: 2,
        username: 'inspector1',
        email: 'inspector@muni.gob.pe',
        role: 'CIUDADANO',
        ruc: '20123456789',
        passwordHash: '',
    );

    $pair = $service->issue($ciudadano);
    $payload = json_decode(base64_decode(strtr(explode('.', $pair->access)[1], '-_', '+/'), true), true);

    expect($payload['role'])->toBe('CIUDADANO')
        ->and($payload['user_id'])->toBe('2')
        ->and($payload['ruc'])->toBe('20123456789');
});

it('decode() extrae los claims correctamente', function () {
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    $user = new AuthenticatedUser(
        id: 7,
        username: 'operador',
        email: '',
        role: 'CIUDADANO',
        ruc: null,
        passwordHash: '',
    );

    $pair = $service->issue($user);
    $decoded = $service->decode($pair->access);

    expect($decoded->id)->toBe(7)
        ->and($decoded->username)->toBe('operador')
        ->and($decoded->role)->toBe('CIUDADANO')
        ->and($decoded->ruc)->toBeNull();
});

it('lanza TokenInvalido con token corrupto o mal firmado', function () {
    $service = new DjangoJwtTokenService(
        secret: 'correct-secret-12345678901234567890',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    expect(fn () => $service->decode('token.totalmente.invalido'))
        ->toThrow(TokenInvalido::class);
});

it('verify() retorna false con token expirado', function () {
    // Configurar TTL negativo o cero para que expire de inmediato
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: -10,
        refreshTtlSeconds: -10,
    );

    $user = new AuthenticatedUser(1, 'admin', '', 'ADMIN_MUNICIPALIDAD', null, '');
    $pair = $service->issue($user);

    expect($service->verify($pair->access))->toBeFalse();
});

it('refresh() rota AMBOS tokens', function () {
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    $user = new AuthenticatedUser(1, 'admin', '', 'ADMIN_MUNICIPALIDAD', null, '');
    $initialPair = $service->issue($user);

    $rotatedPair = $service->refresh($initialPair->refresh);

    expect($rotatedPair->access)->not->toBe($initialPair->access)
        ->and($rotatedPair->refresh)->not->toBe($initialPair->refresh)
        ->and($service->verify($rotatedPair->access))->toBeTrue()
        ->and($service->verify($rotatedPair->refresh))->toBeTrue();
});

it('refresh() rechaza un access token', function () {
    $service = new DjangoJwtTokenService(
        secret: 'django-insecure-dev-key-change-in-production',
        accessTtlSeconds: 3600,
        refreshTtlSeconds: 604800,
    );

    $user = new AuthenticatedUser(1, 'admin', '', 'ADMIN_MUNICIPALIDAD', null, '');
    $pair = $service->issue($user);

    // Intentar refrescar usando el access token en vez del refresh token debe fallar
    expect(fn () => $service->refresh($pair->access))->toThrow(TokenInvalido::class);
});
