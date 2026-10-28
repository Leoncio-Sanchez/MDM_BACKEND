<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Feature\Presentation;

use App\Models\User;
use App\Modules\Archivos\Domain\Contracts\ExpedienteArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'is_superuser' => true, // todos los accesos del perfil
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@muni.gob.pe',
        'password' => 'secret',
    ]));
    $this->user = User::find(1);
});

it('upload: sube archivo y devuelve metadata con 10 keys planas', function () {
    Storage::fake('media');
    DB::table('expedientes')->insert(\Tests\Fixtures\Registros::expediente(['id' => 1]));

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/1/archivos/', [
            'archivo' => UploadedFile::fake()->create('test.pdf', 1, 'application/pdf'),
        ]);

    $response->assertStatus(201)
        ->assertJsonCount(10)
        ->assertJsonStructure([
            'id',
            'uuid',
            'nombre_original',
            'nombre_archivo',
            'ruta_archivo',
            'extension',
            'tamanio_bytes',
            'fecha_digitacion',
            'expediente',
            'usuario',
        ]);
});

it('download: Content-Disposition es inline', function () {
    Storage::fake('media');
    Storage::disk('media')->put('expedientes/1/test.pdf', 'PDF_CONTENT');

    $metadata = new ArchivoMetadata(
        id: 1,
        uuid: '11111111-1111-1111-1111-111111111111',
        nombreOriginal: 'test.pdf',
        nombreArchivo: '11111111-1111-1111-1111-111111111111.pdf',
        rutaArchivo: 'expedientes/1/test.pdf',
        extension: 'pdf',
        tamanioBytes: strlen('PDF_CONTENT'),
        fechaDigitacion: new DateTimeImmutable,
        parentId: 1,
        usuarioId: 1,
    );

    $repo = Mockery::mock(ExpedienteArchivoRepositoryInterface::class);
    $repo->shouldReceive('obtenerPorUuid')
        ->with('11111111-1111-1111-1111-111111111111')
        ->andReturn($metadata);
    $this->app->instance(ExpedienteArchivoRepositoryInterface::class, $repo);

    $response = $this->actingAs($this->user, 'api')
        ->get("/api/lf-itse/expedientes/archivos/{$metadata->uuid}/descargar/");

    $response->assertStatus(200)
        ->assertHeader('Content-Disposition', 'inline; filename="test.pdf"')
        ->assertHeader('Content-Type', 'application/pdf');
});

it('upload sin archivo devuelve 400 (no 422)', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/1/archivos/', []);

    $response->assertStatus(400);
});

it('download con uuid inexistente devuelve 404', function () {
    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/archivos/00000000-0000-0000-0000-000000000000/descargar/');

    $response->assertStatus(404);
});

it('delete con id inexistente devuelve 404', function () {
    $response = $this->actingAs($this->user, 'api')
        ->deleteJson('/api/lf-itse/expedientes/archivos/999999/');

    $response->assertStatus(404);
});
