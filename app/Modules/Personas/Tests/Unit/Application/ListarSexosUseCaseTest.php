<?php

declare(strict_types=1);
use App\Modules\Personas\Application\UseCases\ListarSexosUseCase;

it('lista los sexos disponibles', function () {
    $useCase = new ListarSexosUseCase;
    $result = $useCase->execute();
    expect($result)->toHaveCount(3);
    expect($result[0]['value'])->toBe('M');
});

it('lista con label prefiero no decirlo', function () {
    $useCase = new ListarSexosUseCase;
    $result = $useCase->execute();
    expect($result[2]['label'])->toBe('Prefiero no decirlo');
});
