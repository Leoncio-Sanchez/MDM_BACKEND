<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Application;

use App\Modules\Seguridad\Application\DTOs\VerifyTokenDTO;
use App\Modules\Seguridad\Application\UseCases\VerificarTokenUseCase;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use Mockery;

it('verifica token válido retornando true', function () {
    $tokens = Mockery::mock(TokenServiceInterface::class);
    $tokens->shouldReceive('verify')
        ->once()
        ->with('valid-token-123')
        ->andReturn(true);

    $useCase = new VerificarTokenUseCase($tokens);
    $result = $useCase->execute(new VerifyTokenDTO('valid-token-123'));

    expect($result)->toBeTrue();
});

it('verifica token inválido retornando false', function () {
    $tokens = Mockery::mock(TokenServiceInterface::class);
    $tokens->shouldReceive('verify')
        ->once()
        ->with('bad-token')
        ->andReturn(false);

    $useCase = new VerificarTokenUseCase($tokens);
    $result = $useCase->execute(new VerifyTokenDTO('bad-token'));

    expect($result)->toBeFalse();
});

it('VerifyTokenDTO construye correctamente desde array', function () {
    $dto = VerifyTokenDTO::fromArray(['token' => 'jwt-test-token']);
    expect($dto->token)->toBe('jwt-test-token');
});
