<?php

declare(strict_types=1);

use App\Services\AuthenticatedUser;
use App\Services\DjangoJwtTokenService;

it('emite access y refresh con claims exactos y user_id como STRING', function (): void {
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

    $accessPayload = json_decode((string) base64_decode(strtr(explode('.', $pair->access)[1], '-_', '+/'), true), true);
    expect($accessPayload['token_type'])->toBe('access')
        ->and($accessPayload['user_id'])->toBe('1')
        ->and($accessPayload['username'])->toBe('admin')
        ->and($accessPayload['role'])->toBe('ADMIN_MUNICIPALIDAD')
        ->and($accessPayload['ruc'])->toBeNull()
        ->and(strlen((string) $accessPayload['jti']))->toBe(32)
        ->and($accessPayload['exp'])->toBeGreaterThan($accessPayload['iat']);

    $refreshPayload = json_decode((string) base64_decode(strtr(explode('.', $pair->refresh)[1], '-_', '+/'), true), true);
    expect($refreshPayload['token_type'])->toBe('refresh')
        ->and($refreshPayload['user_id'])->toBe('1')
        ->and($refreshPayload['username'])->toBe('admin')
        ->and($refreshPayload['role'])->toBe('ADMIN_MUNICIPALIDAD')
        ->and($refreshPayload['exp'])->toBeGreaterThan($accessPayload['exp']);
});

it('role asignado correctamente según AuthenticatedUser (CIUDADANO o ADMIN_MUNICIPALIDAD)', function (): void {
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
        ruc: null,
        passwordHash: '',
    );

    $pair = $service->issue($ciudadano);
    $accessPayload = json_decode((string) base64_decode(strtr(explode('.', $pair->access)[1], '-_', '+/'), true), true);

    expect($accessPayload['role'])->toBe('CIUDADANO');
});

it('refresh rota correctamente generando nuevo par access/refresh', function (): void {
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

    $initialPair = $service->issue($user);
    $rotatedPair = $service->refresh($initialPair->refresh);

    expect($rotatedPair->access)->toBeString()->not->toBeEmpty()
        ->and($rotatedPair->refresh)->toBeString()->not->toBeEmpty();
});

it('verify retorna true para token válido y false para inválido', function (): void {
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

    expect($service->verify($pair->access))->toBeTrue()
        ->and($service->verify('token.falso.invalido'))->toBeFalse()
        ->and($service->verify(''))->toBeFalse();
});
