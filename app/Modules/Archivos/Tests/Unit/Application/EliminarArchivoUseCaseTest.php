<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Application;

use App\Modules\Archivos\Application\UseCases\EliminarArchivoUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use DateTimeImmutable;
use Mockery;
use RuntimeException;
use Tests\TestCase;

uses(TestCase::class);

it('elimina registro en BD y archivo físico de disco exitosamente', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $metadata = new ArchivoMetadata(
        id: 5,
        uuid: 'uuid-5',
        nombreOriginal: 'plano.dwg',
        nombreArchivo: 'uuid-5.dwg',
        rutaArchivo: 'itse/2/uuid-5.dwg',
        extension: 'dwg',
        tamanioBytes: 500000,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 2,
        usuarioId: 1,
    );

    $repo->shouldReceive('obtenerPorId')
        ->once()
        ->with(5)
        ->andReturn($metadata);

    $repo->shouldReceive('eliminar')
        ->once()
        ->with(5);

    $manager->shouldReceive('eliminar')
        ->once()
        ->with('itse/2/uuid-5.dwg');

    $useCase = new EliminarArchivoUseCase($manager);
    $useCase->execute($repo, 5);

    expect(true)->toBeTrue();
});

it('lanza ArchivoNoEncontrado si el id no existe en BD', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $repo->shouldReceive('obtenerPorId')
        ->once()
        ->with(404)
        ->andReturnNull();

    $useCase = new EliminarArchivoUseCase($manager);

    expect(fn () => $useCase->execute($repo, 404))
        ->toThrow(ArchivoNoEncontrado::class, 'El archivo con id 404 no existe.');
});

it('NO falla si el archivo físico en disco falla al eliminarse (best effort con report)', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $metadata = new ArchivoMetadata(
        id: 7,
        uuid: 'uuid-7',
        nombreOriginal: 'resolucion.pdf',
        nombreArchivo: 'uuid-7.pdf',
        rutaArchivo: 'licencias_funcionamiento/1/uuid-7.pdf',
        extension: 'pdf',
        tamanioBytes: 15000,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 1,
        usuarioId: 1,
    );

    $repo->shouldReceive('obtenerPorId')
        ->once()
        ->with(7)
        ->andReturn($metadata);

    $repo->shouldReceive('eliminar')
        ->once()
        ->with(7);

    $manager->shouldReceive('eliminar')
        ->once()
        ->with('licencias_funcionamiento/1/uuid-7.pdf')
        ->andThrow(new RuntimeException('Error de permisos en disco'));

    $useCase = new EliminarArchivoUseCase($manager);

    // Debe completarse sin lanzar excepción (best-effort como en Django)
    $useCase->execute($repo, 7);

    expect(true)->toBeTrue();
});
