<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Infrastructure\Persistence;

use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentItseArchivoRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentItseArchivoRepository;
});

it('crea un registro de archivo de itse', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'itse_plano.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "itse/5/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 3000,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 5,
        usuarioId: 1,
    );

    $creado = $this->repo->crear($metadata);

    expect($creado->id)->not->toBeNull()
        ->and($creado->uuid)->toBe($uuid)
        ->and($creado->nombreOriginal)->toBe('itse_plano.pdf')
        ->and($creado->parentId)->toBe(5)
        ->and($creado->usuarioId)->toBe(1);
});

it('obtiene archivo de itse por id y uuid', function () {
    $uuid = (string) Str::uuid();
    $metadata = new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'itse_doc.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "itse/5/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 1500,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 5,
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

it('lista archivos de itse ordenados por fecha digitacion asc', function () {
    $uuid1 = (string) Str::uuid();
    $uuid2 = (string) Str::uuid();

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid2,
        nombreOriginal: 'segundo_itse.pdf',
        nombreArchivo: "{$uuid2}.pdf",
        rutaArchivo: "itse/5/{$uuid2}.pdf",
        extension: 'pdf',
        tamanioBytes: 200,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 15:00:00'),
        parentId: 5,
        usuarioId: 1,
    ));

    $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid1,
        nombreOriginal: 'primero_itse.pdf',
        nombreArchivo: "{$uuid1}.pdf",
        rutaArchivo: "itse/5/{$uuid1}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 09:00:00'),
        parentId: 5,
        usuarioId: 1,
    ));

    $lista = $this->repo->listarPorParent(5);

    expect($lista)->toHaveCount(2)
        ->and($lista[0]->nombreOriginal)->toBe('primero_itse.pdf')
        ->and($lista[1]->nombreOriginal)->toBe('segundo_itse.pdf');
});

it('inactiva el archivo de itse sin borrarlo y lo lee con su constancia', function () {
    \Illuminate\Support\Facades\DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos(['id' => 9, 'username' => 'mesa_partes', 'first_name' => 'ROSA', 'last_name' => 'DIAZ']));
    $uuid = (string) Str::uuid();
    $creado = $this->repo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'eliminar_itse.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "itse/5/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 10:00:00'),
        parentId: 5,
        usuarioId: 1,
    ));

    expect($creado->estaActivo())->toBeTrue();

    $this->repo->inactivar((int) $creado->id, new \App\Modules\Archivos\Domain\Entities\InactivacionArchivo(
        motivo: 'Se subió el documento equivocado',
        fechaInactivacion: new DateTimeImmutable('2026-09-25 10:00:00-05:00'),
        usuarioId: 9,
    ));

    $leido = $this->repo->obtenerPorId((int) $creado->id);
    expect($leido)->not->toBeNull()
        ->and($leido?->estaActivo())->toBeFalse()
        ->and($leido?->inactivacion?->motivo)->toBe('Se subió el documento equivocado')
        ->and($leido?->inactivacion?->usuarioNombre)->toBe('ROSA DIAZ')
        ->and($this->repo->listarPorParent($creado->parentId)[0]->estaActivo())->toBeFalse();
});
