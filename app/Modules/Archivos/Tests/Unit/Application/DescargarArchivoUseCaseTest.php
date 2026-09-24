<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Application;

use App\Modules\Archivos\Application\UseCases\DescargarArchivoUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ArchivoFisicoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

it('retorna metadata y contenido binario cuando el archivo existe', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $metadata = new ArchivoMetadata(
        id: 11,
        uuid: '550e8400-e29b-41d4-a716-446655440000',
        nombreOriginal: 'informe.pdf',
        nombreArchivo: '550e8400.pdf',
        rutaArchivo: 'expedientes/8/550e8400.pdf',
        extension: 'pdf',
        tamanioBytes: 25000,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 8,
        usuarioId: 1,
    );

    $repo->shouldReceive('obtenerPorUuid')
        ->once()
        ->with('550e8400-e29b-41d4-a716-446655440000')
        ->andReturn($metadata);

    $manager->shouldReceive('existe')
        ->once()
        ->with('expedientes/8/550e8400.pdf')
        ->andReturnTrue();

    $manager->shouldReceive('leer')
        ->once()
        ->with('expedientes/8/550e8400.pdf')
        ->andReturn('%PDF-1.4 binary content...');

    $useCase = new DescargarArchivoUseCase($manager);
    $resultado = $useCase->execute($repo, '550e8400-e29b-41d4-a716-446655440000');

    expect($resultado['metadata']->uuid)->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($resultado['contenido'])->toBe('%PDF-1.4 binary content...');
});

it('lanza ArchivoNoEncontrado si el uuid no existe en BD', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $repo->shouldReceive('obtenerPorUuid')
        ->once()
        ->with('uuid-inexistente')
        ->andReturnNull();

    $useCase = new DescargarArchivoUseCase($manager);

    expect(fn () => $useCase->execute($repo, 'uuid-inexistente'))
        ->toThrow(ArchivoNoEncontrado::class, 'El archivo con uuid uuid-inexistente no existe.');
});

it('lanza ArchivoFisicoNoEncontrado si el archivo físico no existe en disco', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $metadata = new ArchivoMetadata(
        id: 12,
        uuid: 'uuid-12',
        nombreOriginal: 'foto.jpg',
        nombreArchivo: 'uuid-12.jpg',
        rutaArchivo: 'itse/3/uuid-12.jpg',
        extension: 'jpg',
        tamanioBytes: 3000,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 3,
        usuarioId: 1,
    );

    $repo->shouldReceive('obtenerPorUuid')
        ->once()
        ->with('uuid-12')
        ->andReturn($metadata);

    $manager->shouldReceive('existe')
        ->once()
        ->with('itse/3/uuid-12.jpg')
        ->andReturnFalse();

    $useCase = new DescargarArchivoUseCase($manager);

    expect(fn () => $useCase->execute($repo, 'uuid-12'))
        ->toThrow(ArchivoFisicoNoEncontrado::class, 'El archivo físico no se encontró en el servidor.');
});
