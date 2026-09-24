<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Application;

use App\Modules\Archivos\Application\UseCases\ListarArchivosUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

it('retorna los archivos del parent ordenados', function () {
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $archivos = [
        new ArchivoMetadata(
            id: 1,
            uuid: 'uuid-1',
            nombreOriginal: 'doc1.pdf',
            nombreArchivo: 'uuid-1.pdf',
            rutaArchivo: 'expedientes/10/uuid-1.pdf',
            extension: 'pdf',
            tamanioBytes: 1000,
            fechaDigitacion: new DateTimeImmutable('2026-09-20 10:00:00'),
            parentId: 10,
            usuarioId: 1,
        ),
        new ArchivoMetadata(
            id: 2,
            uuid: 'uuid-2',
            nombreOriginal: 'doc2.pdf',
            nombreArchivo: 'uuid-2.pdf',
            rutaArchivo: 'expedientes/10/uuid-2.pdf',
            extension: 'pdf',
            tamanioBytes: 2000,
            fechaDigitacion: new DateTimeImmutable('2026-09-20 11:00:00'),
            parentId: 10,
            usuarioId: 1,
        ),
    ];

    $repo->shouldReceive('listarPorParent')
        ->once()
        ->with(10)
        ->andReturn($archivos);

    $useCase = new ListarArchivosUseCase;
    $resultado = $useCase->execute($repo, 10);

    expect($resultado)->toHaveCount(2)
        ->and($resultado[0]->id)->toBe(1)
        ->and($resultado[1]->id)->toBe(2);
});

it('retorna array vacío si no hay archivos asociados', function () {
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $repo->shouldReceive('listarPorParent')
        ->once()
        ->with(999)
        ->andReturn([]);

    $useCase = new ListarArchivosUseCase;
    $resultado = $useCase->execute($repo, 999);

    expect($resultado)->toBeArray()->toBeEmpty();
});
