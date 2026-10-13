<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Feature\Presentation;

use App\Models\User;
use App\Modules\Archivos\Domain\Contracts\ExpedienteArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\ItseArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\LicenciaFuncionamientoArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');

    DB::table('auth_user')->insert(\Tests\Fixtures\UsuarioAuth::datos([
        'is_staff' => true,
        'is_superuser' => true, // todos los accesos del perfil
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@muni.gob.pe',
        'password' => 'secret',
    ]));
    $this->user = User::find(1);

    $this->expRepo = app(ExpedienteArchivoRepositoryInterface::class);
    $this->itseRepo = app(ItseArchivoRepositoryInterface::class);
    $this->licRepo = app(LicenciaFuncionamientoArchivoRepositoryInterface::class);
});

// ═══════════════════════════════════════════════════════════════════════════
// AUTENTICACIÓN
// ═══════════════════════════════════════════════════════════════════════════

it('rechaza acceso sin autenticacion a las rutas de archivos', function () {
    $this->getJson('/api/lf-itse/expedientes/1/archivos/')->assertStatus(401);
    $this->postJson('/api/lf-itse/expedientes/1/archivos/')->assertStatus(401);
    $this->deleteJson('/api/lf-itse/expedientes/archivos/1/')->assertStatus(401);
    $this->getJson('/api/lf-itse/expedientes/archivos/'.Str::uuid().'/descargar/')->assertStatus(401);
});

// ═══════════════════════════════════════════════════════════════════════════
// EXPEDIENTES
// ═══════════════════════════════════════════════════════════════════════════

it('expedientes: lista archivos exitosamente ordenados por fecha', function () {
    $uuid1 = (string) Str::uuid();
    $uuid2 = (string) Str::uuid();

    $this->expRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid2,
        nombreOriginal: 'b.pdf',
        nombreArchivo: "{$uuid2}.pdf",
        rutaArchivo: "expedientes/10/{$uuid2}.pdf",
        extension: 'pdf',
        tamanioBytes: 200,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 12:00:00'),
        parentId: 10,
        usuarioId: 1,
    ));

    $this->expRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid1,
        nombreOriginal: 'a.pdf',
        nombreArchivo: "{$uuid1}.pdf",
        rutaArchivo: "expedientes/10/{$uuid1}.pdf",
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable('2026-09-24 08:00:00'),
        parentId: 10,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/expedientes/10/archivos/');

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonPath('0.nombre_original', 'a.pdf')
        ->assertJsonPath('0.expediente', 10)
        ->assertJsonPath('0.usuario', 1)
        ->assertJsonPath('1.nombre_original', 'b.pdf');
});

it('expedientes: sube archivo exitosamente y devuelve 201 con metadata plana', function () {
    $file = UploadedFile::fake()->create('solicitud.pdf', 300, 'application/pdf');

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/15/archivos/', [
            'archivo' => $file,
        ]);

    $response->assertStatus(201)
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
        ])
        ->assertJsonPath('nombre_original', 'solicitud.pdf')
        ->assertJsonPath('extension', 'pdf')
        ->assertJsonPath('expediente', 15)
        ->assertJsonPath('usuario', 1);

    $ruta = (string) $response->json('ruta_archivo');
    Storage::disk('media')->assertExists($ruta);
});

it('expedientes: falla subir archivo sin archivo y devuelve 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/expedientes/15/archivos/', []);

    $response->assertStatus(400)
        ->assertJsonStructure(['archivo']);
});

it('expedientes: elimina archivo exitosamente con 204', function () {
    $uuid = (string) Str::uuid();
    $ruta = "expedientes/15/{$uuid}.pdf";
    Storage::disk('media')->put($ruta, 'test content');

    $creado = $this->expRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'a_borrar.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: $ruta,
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 15,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/expedientes/archivos/{$creado->id}/");

    $response->assertStatus(204);
    Storage::disk('media')->assertMissing($ruta);
    expect($this->expRepo->obtenerPorId((int) $creado->id))->toBeNull();
});

it('expedientes: falla eliminar con id inexistente y devuelve 404', function () {
    $response = $this->actingAs($this->user, 'api')
        ->deleteJson('/api/lf-itse/expedientes/archivos/999999/');

    $response->assertStatus(404)
        ->assertJsonStructure(['error']);
});

it('expedientes: descarga archivo con headers inline y content-type', function () {
    $uuid = (string) Str::uuid();
    $ruta = "expedientes/15/{$uuid}.pdf";
    Storage::disk('media')->put($ruta, '%PDF-1.4 contenido binario');

    $this->expRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'documento_importante.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: $ruta,
        extension: 'pdf',
        tamanioBytes: strlen('%PDF-1.4 contenido binario'),
        fechaDigitacion: new DateTimeImmutable,
        parentId: 15,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->get("/api/lf-itse/expedientes/archivos/{$uuid}/descargar/");

    $response->assertStatus(200)
        ->assertHeader('Content-Disposition', 'inline; filename="documento_importante.pdf"')
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->streamedContent())->toBe('%PDF-1.4 contenido binario');
});

it('expedientes: falla descargar con uuid inexistente y devuelve 404', function () {
    $uuid = (string) Str::uuid();

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/lf-itse/expedientes/archivos/{$uuid}/descargar/");

    $response->assertStatus(404)
        ->assertJsonStructure(['error']);
});

it('expedientes: falla descargar si archivo fisico no existe y devuelve 404 con mensaje exacto', function () {
    $uuid = (string) Str::uuid();

    $this->expRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'fantasma.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "expedientes/15/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 50,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 15,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->getJson("/api/lf-itse/expedientes/archivos/{$uuid}/descargar/");

    $response->assertStatus(404)
        ->assertJson(['error' => 'El archivo físico no se encontró en el servidor.']);
});

// ═══════════════════════════════════════════════════════════════════════════
// ITSE
// ═══════════════════════════════════════════════════════════════════════════

it('itse: lista archivos exitosamente', function () {
    $uuid = (string) Str::uuid();
    $this->itseRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'plano.png',
        nombreArchivo: "{$uuid}.png",
        rutaArchivo: "itse/3/{$uuid}.png",
        extension: 'png',
        tamanioBytes: 500,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 3,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/3/archivos/');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.nombre_original', 'plano.png')
        ->assertJsonPath('0.itse', 3)
        ->assertJsonPath('0.usuario', 1);
});

it('itse: sube archivo exitosamente y devuelve 201', function () {
    $file = UploadedFile::fake()->create('inspeccion.jpg', 250, 'image/jpeg');

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/itse/3/archivos/', [
            'archivo' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('nombre_original', 'inspeccion.jpg')
        ->assertJsonPath('itse', 3);

    $ruta = (string) $response->json('ruta_archivo');
    Storage::disk('media')->assertExists($ruta);
});

it('itse: falla subir archivo sin archivo con 400', function () {
    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/itse/3/archivos/', []);

    $response->assertStatus(400);
});

it('itse: elimina archivo exitosamente con 204', function () {
    $uuid = (string) Str::uuid();
    $ruta = "itse/3/{$uuid}.pdf";
    Storage::disk('media')->put($ruta, 'itse test');

    $creado = $this->itseRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'borrar_itse.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: $ruta,
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 3,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/itse/archivos/{$creado->id}/");

    $response->assertStatus(204);
});

it('itse: falla eliminar con id inexistente con 404', function () {
    $this->actingAs($this->user, 'api')
        ->deleteJson('/api/lf-itse/itse/archivos/999999/')
        ->assertStatus(404);
});

it('itse: descarga archivo con headers inline y content-type', function () {
    $uuid = (string) Str::uuid();
    $ruta = "itse/3/{$uuid}.png";
    Storage::disk('media')->put($ruta, 'imagen png binaria');

    $this->itseRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'diagrama.png',
        nombreArchivo: "{$uuid}.png",
        rutaArchivo: $ruta,
        extension: 'png',
        tamanioBytes: strlen('imagen png binaria'),
        fechaDigitacion: new DateTimeImmutable,
        parentId: 3,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->get("/api/lf-itse/itse/archivos/{$uuid}/descargar/");

    $response->assertStatus(200)
        ->assertHeader('Content-Disposition', 'inline; filename="diagrama.png"')
        ->assertHeader('Content-Type', 'image/png');
});

it('itse: falla descargar con uuid inexistente con 404', function () {
    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/itse/archivos/'.Str::uuid().'/descargar/')
        ->assertStatus(404);
});

// ═══════════════════════════════════════════════════════════════════════════
// LICENCIAS DE FUNCIONAMIENTO
// ═══════════════════════════════════════════════════════════════════════════

it('licencias: lista archivos exitosamente', function () {
    $uuid = (string) Str::uuid();
    $this->licRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'croquis.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: "licencias_funcionamiento/7/{$uuid}.pdf",
        extension: 'pdf',
        tamanioBytes: 400,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 7,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/licencias-funcionamiento/7/archivos/');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonPath('0.nombre_original', 'croquis.pdf')
        ->assertJsonPath('0.licencia_funcionamiento', 7)
        ->assertJsonPath('0.usuario', 1);
});

it('licencias: sube archivo exitosamente y devuelve 201', function () {
    $file = UploadedFile::fake()->create('declaracion.pdf', 350, 'application/pdf');

    $response = $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/licencias-funcionamiento/7/archivos/', [
            'archivo' => $file,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('nombre_original', 'declaracion.pdf')
        ->assertJsonPath('licencia_funcionamiento', 7);

    $ruta = (string) $response->json('ruta_archivo');
    Storage::disk('media')->assertExists($ruta);
});

it('licencias: falla subir archivo sin archivo con 400', function () {
    $this->actingAs($this->user, 'api')
        ->postJson('/api/lf-itse/licencias-funcionamiento/7/archivos/', [])
        ->assertStatus(400);
});

it('licencias: elimina archivo exitosamente con 204', function () {
    $uuid = (string) Str::uuid();
    $ruta = "licencias_funcionamiento/7/{$uuid}.pdf";
    Storage::disk('media')->put($ruta, 'lic test');

    $creado = $this->licRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'borrar_lic.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: $ruta,
        extension: 'pdf',
        tamanioBytes: 100,
        fechaDigitacion: new DateTimeImmutable,
        parentId: 7,
        usuarioId: 1,
    ));

    $this->actingAs($this->user, 'api')
        ->deleteJson("/api/lf-itse/licencias-funcionamiento/archivos/{$creado->id}/")
        ->assertStatus(204);
});

it('licencias: falla eliminar con id inexistente con 404', function () {
    $this->actingAs($this->user, 'api')
        ->deleteJson('/api/lf-itse/licencias-funcionamiento/archivos/999999/')
        ->assertStatus(404);
});

it('licencias: descarga archivo con headers inline y content-type', function () {
    $uuid = (string) Str::uuid();
    $ruta = "licencias_funcionamiento/7/{$uuid}.pdf";
    Storage::disk('media')->put($ruta, 'declaracion jurada pdf');

    $this->licRepo->crear(new ArchivoMetadata(
        id: null,
        uuid: $uuid,
        nombreOriginal: 'declaracion_final.pdf',
        nombreArchivo: "{$uuid}.pdf",
        rutaArchivo: $ruta,
        extension: 'pdf',
        tamanioBytes: strlen('declaracion jurada pdf'),
        fechaDigitacion: new DateTimeImmutable,
        parentId: 7,
        usuarioId: 1,
    ));

    $response = $this->actingAs($this->user, 'api')
        ->get("/api/lf-itse/licencias-funcionamiento/archivos/{$uuid}/descargar/");

    $response->assertStatus(200)
        ->assertHeader('Content-Disposition', 'inline; filename="declaracion_final.pdf"')
        ->assertHeader('Content-Type', 'application/pdf');
});

it('licencias: falla descargar con uuid inexistente con 404', function () {
    $this->actingAs($this->user, 'api')
        ->getJson('/api/lf-itse/licencias-funcionamiento/archivos/'.Str::uuid().'/descargar/')
        ->assertStatus(404);
});
