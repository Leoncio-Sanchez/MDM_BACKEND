<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Application;

use App\Modules\Seguridad\Application\DTOs\RefreshTokenDTO;
use App\Modules\Seguridad\Application\UseCases\RefrescarTokenUseCase;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\Exceptions\TokenInvalido;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;
use Mockery;

it('refresca token devolviendo nuevo par TokenPair (access + refresh)', function () {
    $tokens = Mockery::mock(TokenServiceInterface::class);
    $tokens->shouldReceive('refresh')
        ->once()
        ->with('old-refresh-token')
        ->andReturn(new TokenPair(access: 'new-access', refresh: 'new-refresh'));

    $useCase = new RefrescarTokenUseCase($tokens);
    $result = $useCase->execute(new RefreshTokenDTO('old-refresh-token'));

    expect($result->access)->toBe('new-access')
        ->and($result->refresh)->toBe('new-refresh');
});

it('propaga TokenInvalido si el token de refresco falla', function () {
    $tokens = Mockery::mock(TokenServiceInterface::class);
    $tokens->shouldReceive('refresh')
        ->once()
        ->with('invalid-token')
        ->andThrow(TokenInvalido::default());

    $useCase = new RefrescarTokenUseCase($tokens);

    expect(fn () => $useCase->execute(new RefreshTokenDTO('invalid-token')))
        ->toThrow(TokenInvalido::class);
});

it('RefreshTokenDTO construye correctamente desde array', function () {
    $dto = RefreshTokenDTO::fromArray(['refresh' => 'refresh-token-xyz']);
    expect($dto->refresh)->toBe('refresh-token-xyz');
});
