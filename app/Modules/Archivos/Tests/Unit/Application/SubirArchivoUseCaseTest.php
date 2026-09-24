<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Application;

use App\Modules\Archivos\Application\DTOs\SubirArchivoDTO;
use App\Modules\Archivos\Application\UseCases\SubirArchivoUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ErrorAlGuardarArchivo;
use DateTimeImmutable;
use Illuminate\Http\UploadedFile;
use Mockery;
use RuntimeException;
use Tests\TestCase;

uses(TestCase::class);

it('guarda archivo y crea registro exitosamente', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $file = UploadedFile::fake()->create('documento.pdf', 100);

    $manager->shouldReceive('guardar')
        ->once()
        ->with($file, 'expedientes/15')
        ->andReturn([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'nombre_original' => 'documento.pdf',
            'nombre_archivo' => '550e8400-e29b-41d4-a716-446655440000.pdf',
            'ruta_archivo' => 'expedientes/15/550e8400-e29b-41d4-a716-446655440000.pdf',
            'extension' => 'pdf',
            'tamanio_bytes' => 102400,
        ]);

    $savedMetadata = new ArchivoMetadata(
        id: 1,
        uuid: '550e8400-e29b-41d4-a716-446655440000',
        nombreOriginal: 'documento.pdf',
        nombreArchivo: '550e8400-e29b-41d4-a716-446655440000.pdf',
        rutaArchivo: 'expedientes/15/550e8400-e29b-41d4-a716-446655440000.pdf',
        extension: 'pdf',
        tamanioBytes: 102400,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 15,
        usuarioId: 7,
    );

    $repo->shouldReceive('crear')
        ->once()
        ->withArgs(function (ArchivoMetadata $m) {
            return $m->uuid === '550e8400-e29b-41d4-a716-446655440000'
                && $m->parentId === 15
                && $m->usuarioId === 7;
        })
        ->andReturn($savedMetadata);

    $useCase = new SubirArchivoUseCase($manager);
    $dto = new SubirArchivoDTO(
        parentId: 15,
        archivo: $file,
        usuarioId: 7,
        carpetaBase: 'expedientes',
    );

    $resultado = $useCase->execute($repo, $dto);

    expect($resultado->id)->toBe(1)
        ->and($resultado->nombreOriginal)->toBe('documento.pdf')
        ->and($resultado->parentId)->toBe(15);
});

it('elimina archivo físico si falla la creación en BD (compensación atómica)', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $file = UploadedFile::fake()->create('foto.png', 50);

    $manager->shouldReceive('guardar')
        ->once()
        ->with($file, 'itse/9')
        ->andReturn([
            'uuid' => 'uuid-itse-1',
            'nombre_original' => 'foto.png',
            'nombre_archivo' => 'uuid-itse-1.png',
            'ruta_archivo' => 'itse/9/uuid-itse-1.png',
            'extension' => 'png',
            'tamanio_bytes' => 51200,
        ]);

    $repo->shouldReceive('crear')
        ->once()
        ->andThrow(new RuntimeException('Error de conexión a PostgreSQL Neon'));

    $manager->shouldReceive('eliminar')
        ->once()
        ->with('itse/9/uuid-itse-1.png');

    $useCase = new SubirArchivoUseCase($manager);
    $dto = new SubirArchivoDTO(
        parentId: 9,
        archivo: $file,
        usuarioId: 3,
        carpetaBase: 'itse',
    );

    expect(fn () => $useCase->execute($repo, $dto))
        ->toThrow(ErrorAlGuardarArchivo::class, 'Error al guardar el archivo: Error de conexión a PostgreSQL Neon');
});

it('propaga ErrorAlGuardarArchivo si el manager falla en disco', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $file = UploadedFile::fake()->create('archivo.bin', 10);

    $manager->shouldReceive('guardar')
        ->once()
        ->andThrow(new RuntimeException('Disco lleno'));

    $useCase = new SubirArchivoUseCase($manager);
    $dto = new SubirArchivoDTO(
        parentId: 2,
        archivo: $file,
        usuarioId: 1,
        carpetaBase: 'licencias_funcionamiento',
    );

    expect(fn () => $useCase->execute($repo, $dto))
        ->toThrow(RuntimeException::class, 'Disco lleno');
});

it('genera metadata correcta con parentId y usuarioId', function () {
    $manager = Mockery::mock(ArchivoManagerInterface::class);
    $repo = Mockery::mock(ArchivoRepositoryInterface::class);

    $file = UploadedFile::fake()->create('licencia.pdf', 80);

    $manager->shouldReceive('guardar')
        ->once()
        ->andReturn([
            'uuid' => 'uuid-lic-1',
            'nombre_original' => 'licencia.pdf',
            'nombre_archivo' => 'uuid-lic-1.pdf',
            'ruta_archivo' => 'licencias_funcionamiento/33/uuid-lic-1.pdf',
            'extension' => 'pdf',
            'tamanio_bytes' => 81920,
        ]);

    $repo->shouldReceive('crear')
        ->once()
        ->andReturnUsing(function (ArchivoMetadata $m) {
            return new ArchivoMetadata(
                id: 99,
                uuid: $m->uuid,
                nombreOriginal: $m->nombreOriginal,
                nombreArchivo: $m->nombreArchivo,
                rutaArchivo: $m->rutaArchivo,
                extension: $m->extension,
                tamanioBytes: $m->tamanioBytes,
                fechaDigitacion: $m->fechaDigitacion,
                parentId: $m->parentId,
                usuarioId: $m->usuarioId,
            );
        });

    $useCase = new SubirArchivoUseCase($manager);
    $dto = new SubirArchivoDTO(
        parentId: 33,
        archivo: $file,
        usuarioId: 44,
        carpetaBase: 'licencias_funcionamiento',
    );

    $res = $useCase->execute($repo, $dto);

    expect($res->id)->toBe(99)
        ->and($res->parentId)->toBe(33)
        ->and($res->usuarioId)->toBe(44)
        ->and($res->extension)->toBe('pdf');
});
