<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Infrastructure\Persistence;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentLicenciaFuncionamientoArchivoRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentLicenciaFuncionamientoArchivoRepository;
});

it('crea un registro de archivo de licencia', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'licencia_croquis.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 4096,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 7,
        usuarioId: 1,
    );

    $creado = $this->repo->crear($metadata);

    expect($creado->id)->not->toBeNull()
        ->and($creado->uuid)->toBe($uuid)
        ->and($creado->nombreOriginal)->toBe('licencia_croquis.pdf')
        ->and($creado->parentId)->toBe(7)
        ->and($creado->usuarioId)->toBe(1);
});

it('obtiene archivo de licencia por id y uuid', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'licencia_doc.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 1500,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 7,
        usuarioId: 1,
    );

    $creado = $this->repo->crear($metadata);

    $porId = $this->repo->obtenerPorId((int) $creado->id);
    $porUuid = $this->repo->obtenerPorUuid($uuid);

    expect($porId)->not->toBeNull()
        ->and($porId?->uuid)->toBe($uuid)
        ->and($porUuid)->not->toBeNull()
        ->and($porUuid?->id)->toBe($creado->id);
});

it('lista archivos de licencia ordenados por fecha digitacion asc', function () {
    $uuid1 = (string) Str::uuid();
    $uuid2 = (string) Str::uuid();

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid2,
        nombreOriginal: 'segundo_lic.pdf',
        nombreArchivo: "{$uuid2}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid2}.pdf",
        extension: 'pdf',
        tamanioBytes: 200,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 16:00:00'),
        parentId: 7,
        usuarioId: 1,
    ));

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid1,
        nombreOriginal: 'primero_lic.pdf',
        nombreArchivo: "{$uuid1}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid1}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 07:00:00'),
        parentId: 7,
        usuarioId: 1,
    ));

    $lista = $this->repo->listarPorParent(7);

    expect($lista)->toHaveCount(2)
        ->and($lista[0]->nombreOriginal)->toBe('primero_lic.pdf')
        ->and($lista[1]->nombreOriginal)->toBe('segundo_lic.pdf');
});

it('elimina registro de archivo de licencia', function () {
    $uuid = (string) Str::uuid();
    $creado = $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'eliminar_lic.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 7,
        usuarioId: 1,
    ));

    $this->repo->eliminar((int) $creado->id);

    expect($this->repo->obtenerPorId((int) $creado->id))->toBeNull();
});
