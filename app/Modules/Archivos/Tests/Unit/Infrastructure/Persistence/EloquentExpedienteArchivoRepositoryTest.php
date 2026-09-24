<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Infrastructure\Persistence;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentExpedienteArchivoRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentExpedienteArchivoRepository;
});

it('crea un registro de archivo de expediente', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'expediente_001.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "expedientes/10/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 2048,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 10,
        usuarioId: 1,
    );

    $creado = $this->repo->crear($metadata);

    expect($creado->id)->not->toBeNull()
        ->and($creado->uuid)->toBe($uuid)
        ->and($creado->nombreOriginal)->toBe('expediente_001.pdf')
        ->and($creado->parentId)->toBe(10)
        ->and($creado->usuarioId)->toBe(1);
});

it('obtiene archivo de expediente por id', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'doc.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "expedientes/10/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 1024,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 10,
        usuarioId: 1,
    );

    $creado = $this->repo->crear($metadata);

    $encontrado = $this->repo->obtenerPorId((int) $creado->id);

    expect($encontrado)->not->toBeNull()
        ->and($encontrado?->uuid)->toBe($uuid)
        ->and($encontrado?->nombreOriginal)->toBe('doc.pdf');
});

it('obtiene archivo de expediente por uuid', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'doc.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "expedientes/10/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 1024,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 10,
        usuarioId: 1,
    );

    $this->repo->crear($metadata);

    $encontrado = $this->repo->obtenerPorUuid($uuid);

    expect($encontrado)->not->toBeNull()
        ->and($encontrado?->uuid)->toBe($uuid);
});

it('retorna null si archivo de expediente no existe', function () {
    expect($this->repo->obtenerPorId(999999))->toBeNull()
        ->and($this->repo->obtenerPorUuid((string) Str::uuid()))->toBeNull();
});

it('lista archivos de expediente ordenados por fecha digitacion asc', function () {
    $uuid1 = (string) Str::uuid();
    $uuid2 = (string) Str::uuid();

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid2,
        nombreOriginal: 'segundo.pdf',
        nombreArchivo: "{$uuid2}.pdf",
        rutaArchivo: "expedientes/10/{$uuid2}.pdf",
        extension: 'pdf',
        tamanioBytes: 200,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 12:00:00'),
        parentId: 10,
        usuarioId: 1,
    ));

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid1,
        nombreOriginal: 'primero.pdf',
        nombreArchivo: "{$uuid1}.pdf",
        rutaArchivo: "expedientes/10/{$uuid1}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 08:00:00'),
        parentId: 10,
        usuarioId: 1,
    ));

    $lista = $this->repo->listarPorParent(10);

    expect($lista)->toHaveCount(2)
        ->and($lista[0]->nombreOriginal)->toBe('primero.pdf')
        ->and($lista[1]->nombreOriginal)->toBe('segundo.pdf');
});

it('elimina registro de archivo de expediente', function () {
    $uuid = (string) Str::uuid();
    $creado = $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'eliminar.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "expedientes/10/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 10,
        usuarioId: 1,
    ));

    $this->repo->eliminar((int) $creado->id);

    expect($this->repo->obtenerPorId((int) $creado->id))->toBeNull();
});
