<?php

declare(strict_types=1);

namespace Tests\Feature\Archivos;

use App\Models\Usuario as UsuarioModel;
use App\Services\DjangoJwtTokenService;
use App\Services\AuthenticatedUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ArchivoControllerTest extends TestCase
{
    private string $token;

    private int $expedienteId;

    private int $itseId;

    private int $licenciaId;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        if (! Schema::hasTable('auth_user')) {
            Schema::create('auth_user', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('first_name')->default('');
                $table->string('last_name')->default('');
                $table->string('email')->default('');
                $table->boolean('is_superuser')->default(false);
                $table->boolean('is_staff')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login')->nullable();
                $table->timestamp('date_joined')->nullable();
            });
        }

        if (! Schema::hasTable('expedientes')) {
            Schema::create('expedientes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('numero_expediente', 50)->nullable();
            });
        }

        if (! Schema::hasTable('itse')) {
            Schema::create('itse', function (Blueprint $table) {
                $table->increments('id');
                $table->string('numero_certificado', 50)->nullable();
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento')) {
            Schema::create('licencias_funcionamiento', function (Blueprint $table) {
                $table->increments('id');
                $table->string('numero_licencia', 50)->nullable();
            });
        }

        if (! Schema::hasTable('expedientes_archivos')) {
            Schema::create('expedientes_archivos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->unique();
                $table->string('nombre_original', 255);
                $table->string('nombre_archivo', 255);
                $table->string('ruta_archivo', 255);
                $table->string('extension', 10);
                $table->integer('tamanio_bytes');
                $table->dateTime('fecha_digitacion');
                $table->unsignedInteger('expediente_id');
                $table->unsignedInteger('usuario_id');
            });
        }

        if (! Schema::hasTable('itse_archivos')) {
            Schema::create('itse_archivos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->unique();
                $table->string('nombre_original', 255);
                $table->string('nombre_archivo', 255);
                $table->string('ruta_archivo', 255);
                $table->string('extension', 10);
                $table->integer('tamanio_bytes');
                $table->dateTime('fecha_digitacion');
                $table->unsignedInteger('itse_id');
                $table->unsignedInteger('usuario_id');
            });
        }

        if (! Schema::hasTable('licencias_funcionamiento_archivos')) {
            Schema::create('licencias_funcionamiento_archivos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('uuid', 36)->unique();
                $table->string('nombre_original', 255);
                $table->string('nombre_archivo', 255);
                $table->string('ruta_archivo', 255);
                $table->string('extension', 10);
                $table->integer('tamanio_bytes');
                $table->dateTime('fecha_digitacion');
                $table->unsignedInteger('licencia_funcionamiento_id');
                $table->unsignedInteger('usuario_id');
            });
        }

        $user = UsuarioModel::firstOrCreate(
            ['username' => 'test_archivo_admin'],
            [
                'password' => bcrypt('password'),
                'email' => 'admin@archivo.com',
                'is_superuser' => true,
                'is_staff' => true,
                'is_active' => true,
            ]
        );

        $authUser = new AuthenticatedUser(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            role: 'ADMIN_MUNICIPALIDAD',
            ruc: null,
            passwordHash: $user->password
        );
        $this->token = app(DjangoJwtTokenService::class)->issue($authUser)->access;

        $this->expedienteId = (int) DB::table('expedientes')->insertGetId([
            'numero_expediente' => 'EXP-2026-0001',
        ]);

        $this->itseId = (int) DB::table('itse')->insertGetId([
            'numero_certificado' => 'ITSE-2026-0001',
        ]);

        $this->licenciaId = (int) DB::table('licencias_funcionamiento')->insertGetId([
            'numero_licencia' => 'LF-2026-0001',
        ]);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_requires_authentication(): void
    {
        $this->getJson("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/")
            ->assertStatus(401);

        $this->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/")
            ->assertStatus(401);

        $this->deleteJson('/api/lf-itse/expedientes/archivos/1/')
            ->assertStatus(401);

        $this->getJson('/api/lf-itse/expedientes/archivos/some-uuid/descargar/')
            ->assertStatus(401);
    }

    public function test_subir_archivo_expediente(): void
    {
        $file = UploadedFile::fake()->create('resolucion.pdf', 500, 'application/pdf');

        $response = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", [
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
            ->assertJson([
                'nombre_original' => 'resolucion.pdf',
                'extension' => 'pdf',
                'expediente' => $this->expedienteId,
            ]);

        $rutaArchivo = $response->json('ruta_archivo');
        Storage::disk('media')->assertExists($rutaArchivo);

        $this->assertDatabaseHas('expedientes_archivos', [
            'expediente_id' => $this->expedienteId,
            'nombre_original' => 'resolucion.pdf',
            'extension' => 'pdf',
        ]);
    }

    public function test_subir_archivo_itse(): void
    {
        $file = UploadedFile::fake()->image('plano.png', 200, 200);

        $response = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/itse/{$this->itseId}/archivos/", [
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
                'itse',
                'usuario',
            ])
            ->assertJson([
                'nombre_original' => 'plano.png',
                'extension' => 'png',
                'itse' => $this->itseId,
            ]);

        $rutaArchivo = $response->json('ruta_archivo');
        Storage::disk('media')->assertExists($rutaArchivo);

        $this->assertDatabaseHas('itse_archivos', [
            'itse_id' => $this->itseId,
            'nombre_original' => 'plano.png',
        ]);
    }

    public function test_subir_archivo_licencia(): void
    {
        $file = UploadedFile::fake()->create('declaracion.docx', 300);

        $response = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/archivos/", [
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
                'licencia_funcionamiento',
                'usuario',
            ])
            ->assertJson([
                'nombre_original' => 'declaracion.docx',
                'extension' => 'docx',
                'licencia_funcionamiento' => $this->licenciaId,
            ]);

        $rutaArchivo = $response->json('ruta_archivo');
        Storage::disk('media')->assertExists($rutaArchivo);

        $this->assertDatabaseHas('licencias_funcionamiento_archivos', [
            'licencia_funcionamiento_id' => $this->licenciaId,
            'nombre_original' => 'declaracion.docx',
        ]);
    }

    public function test_listar_archivos_expediente(): void
    {
        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->create('doc2.pdf', 200);

        $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", ['archivo' => $file1]);
        $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", ['archivo' => $file2]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/");

        $response->assertStatus(200)
            ->assertJsonCount(2);

        $this->assertEquals('doc1.pdf', $response->json('0.nombre_original'));
        $this->assertEquals('doc2.pdf', $response->json('1.nombre_original'));
    }

    public function test_listar_archivos_itse(): void
    {
        $file = UploadedFile::fake()->image('itse_doc.jpg');

        $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/itse/{$this->itseId}/archivos/", ['archivo' => $file]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/lf-itse/itse/{$this->itseId}/archivos/");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.itse', $this->itseId);
    }

    public function test_listar_archivos_licencia(): void
    {
        $file = UploadedFile::fake()->create('lf_doc.pdf', 150);

        $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/archivos/", ['archivo' => $file]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/archivos/");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.licencia_funcionamiento', $this->licenciaId);
    }

    public function test_descargar_archivo_con_hash_identico_y_disposition_inline(): void
    {
        $content = 'Contenido binario exacto de prueba para verificar SHA256.';
        $file = UploadedFile::fake()->createWithContent('hash_test.pdf', $content);

        $uploadRes = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", ['archivo' => $file]);

        $uuid = $uploadRes->json('uuid');

        $downloadRes = $this->withHeaders($this->authHeaders())
            ->get("/api/lf-itse/expedientes/archivos/{$uuid}/descargar/");

        $downloadRes->assertStatus(200);
        $downloadRes->assertHeader('Content-Disposition', 'inline; filename="hash_test.pdf"');
        $downloadRes->assertHeader('Content-Type', 'application/pdf');

        $downloadedContent = $downloadRes->streamedContent();
        $this->assertSame(hash('sha256', $content), hash('sha256', $downloadedContent));
    }

    public function test_eliminar_archivo_expediente(): void
    {
        $file = UploadedFile::fake()->create('para_borrar.pdf', 100);

        $uploadRes = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", ['archivo' => $file]);

        $id = $uploadRes->json('id');
        $rutaArchivo = $uploadRes->json('ruta_archivo');

        Storage::disk('media')->assertExists($rutaArchivo);

        $deleteRes = $this->withHeaders($this->authHeaders())
            ->delete("/api/lf-itse/expedientes/archivos/{$id}/");

        $deleteRes->assertStatus(204);

        Storage::disk('media')->assertMissing($rutaArchivo);
        $this->assertDatabaseMissing('expedientes_archivos', ['id' => $id]);
    }

    public function test_eliminar_archivo_itse(): void
    {
        $file = UploadedFile::fake()->create('itse_borrar.pdf', 100);

        $uploadRes = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/itse/{$this->itseId}/archivos/", ['archivo' => $file]);

        $id = $uploadRes->json('id');
        $deleteRes = $this->withHeaders($this->authHeaders())
            ->delete("/api/lf-itse/itse/archivos/{$id}/");

        $deleteRes->assertStatus(204);
        $this->assertDatabaseMissing('itse_archivos', ['id' => $id]);
    }

    public function test_eliminar_archivo_licencia(): void
    {
        $file = UploadedFile::fake()->create('licencia_borrar.pdf', 100);

        $uploadRes = $this->withHeaders($this->authHeaders())
            ->post("/api/lf-itse/licencias-funcionamiento/{$this->licenciaId}/archivos/", ['archivo' => $file]);

        $id = $uploadRes->json('id');
        $deleteRes = $this->withHeaders($this->authHeaders())
            ->delete("/api/lf-itse/licencias-funcionamiento/archivos/{$id}/");

        $deleteRes->assertStatus(204);
        $this->assertDatabaseMissing('licencias_funcionamiento_archivos', ['id' => $id]);
    }

    public function test_404_when_parent_does_not_exist(): void
    {
        $responseExpediente = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/expedientes/99999/archivos/');
        $responseExpediente->assertStatus(404)
            ->assertJson(['detail' => 'No Expediente matches the given query.']);

        $responseItse = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/itse/99999/archivos/');
        $responseItse->assertStatus(404)
            ->assertJson(['detail' => 'No Itse matches the given query.']);

        $responseLicencia = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/licencias-funcionamiento/99999/archivos/');
        $responseLicencia->assertStatus(404)
            ->assertJson(['detail' => 'No LicenciaFuncionamiento matches the given query.']);
    }

    public function test_400_validation_error_when_file_is_missing(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson("/api/lf-itse/expedientes/{$this->expedienteId}/archivos/", []);

        $response->assertStatus(400)
            ->assertJson([
                'archivo' => ['No se envió ningún archivo.'],
            ]);
    }

    public function test_404_when_file_does_not_exist_on_delete_or_download(): void
    {
        $deleteRes = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/lf-itse/expedientes/archivos/99999/');
        $deleteRes->assertStatus(404)
            ->assertJson(['error' => 'Archivo no encontrado.']);

        $downloadRes = $this->withHeaders($this->authHeaders())
            ->getJson('/api/lf-itse/expedientes/archivos/00000000-0000-0000-0000-000000000000/descargar/');
        $downloadRes->assertStatus(404)
            ->assertJson(['error' => 'Archivo no encontrado.']);
    }
}
