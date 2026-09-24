<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Domain;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ArchivoError;
use App\Modules\Archivos\Domain\Exceptions\ArchivoFisicoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ErrorAlGuardarArchivo;
use DateTimeImmutable;
use Tests\TestCase;

uses(TestCase::class);

it('construye ArchivoMetadata con todos los campos requeridos', function () {
    $now = new DateTimeImmutable('2026-09-23 15:30:00');
    $metadata = new ArchivoMetadata(
        id: 1,
        uuid: '550e8400-e29b-41d4-a716-446655440000',
        nombreOriginal: 'solicitud_licencia.pdf',
        nombreArchivo: '550e8400-e29b-41d4-a716-446655440000.pdf',
        rutaArchivo: 'expedientes/27/550e8400-e29b-41d4-a716-446655440000.pdf',
        extension: 'pdf',
        tamanioBytes: 204850,
        fechaDigitacion: $now,
        parentId: 27,
        usuarioId: 1,
    );

    expect($metadata->id)->toBe(1)
        ->and($metadata->uuid)->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($metadata->nombreOriginal)->toBe('solicitud_licencia.pdf')
        ->and($metadata->nombreArchivo)->toBe('550e8400-e29b-41d4-a716-446655440000.pdf')
        ->and($metadata->rutaArchivo)->toBe('expedientes/27/550e8400-e29b-41d4-a716-446655440000.pdf')
        ->and($metadata->extension)->toBe('pdf')
        ->and($metadata->tamanioBytes)->toBe(204850)
        ->and($metadata->fechaDigitacion)->toBe($now)
        ->and($metadata->parentId)->toBe(27)
        ->and($metadata->usuarioId)->toBe(1);
});

it('permite id nulo para archivos en memoria antes de persistir', function () {
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: '550e8400-e29b-41d4-a716-446655440000',
        nombreOriginal: 'plano.png',
        nombreArchivo: 'plano_hash.png',
        rutaArchivo: 'itse/5/plano_hash.png',
        extension: 'png',
        tamanioBytes: 1024,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 5,
        usuarioId: 2,
    );

    expect($metadata->id)->toBeNull();
});

it('ArchivoNoEncontrado porId tiene mensaje correcto', function () {
    $e = ArchivoNoEncontrado::porId(42);

    expect($e)->toBeInstanceOf(ArchivoError::class)
        ->and($e->getMessage())->toBe('El archivo con id 42 no existe.');
});

it('ArchivoNoEncontrado porUuid tiene mensaje correcto', function () {
    $e = ArchivoNoEncontrado::porUuid('uuid-1234');

    expect($e)->toBeInstanceOf(ArchivoError::class)
        ->and($e->getMessage())->toBe('El archivo con uuid uuid-1234 no existe.');
});

it('ArchivoFisicoNoEncontrado tiene mensaje exacto de Django', function () {
    $e = ArchivoFisicoNoEncontrado::enRuta('expedientes/27/archivo.pdf');

    expect($e)->toBeInstanceOf(ArchivoError::class)
        ->and($e->getMessage())->toBe('El archivo físico no se encontró en el servidor.');
});

it('ErrorAlGuardarArchivo formatea detalle del error correctamente', function () {
    $e = ErrorAlGuardarArchivo::porFallo('Permiso denegado en disco');

    expect($e)->toBeInstanceOf(ArchivoError::class)
        ->and($e->getMessage())->toBe('Error al guardar el archivo: Permiso denegado en disco');
});
