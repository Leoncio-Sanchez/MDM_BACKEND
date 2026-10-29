<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->usuario = function (array $perfil): User {
        $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'u_'.uniqid(), 'is_staff' => true]));
        DB::table('usuarios_perfiles')->insert(array_merge(
            ['expedientes' => false, 'licencias' => false, 'itse' => false, 'admin' => false,
                'es_inspector' => false, 'es_jefe_itse' => false, 'reportes_completos' => false],
            $perfil,
            ['user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id],
        ));

        return User::find($id);
    };
    $this->itse = ($this->usuario)(['itse' => true]);
    $this->licencias = ($this->usuario)(['licencias' => true]);

    $tupaAmbos = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => true, 'requiere_lf' => true]));
    $tupaSoloLf = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa(['requiere_itse' => false, 'requiere_lf' => true]));
    $persona = DB::table('personas')->insertGetId(Registros::persona());
    $this->expediente = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 501, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupaAmbos,
    ]));
    $this->expSoloLf = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 502, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupaSoloLf,
    ]));

    $this->denegar = fn (User $quien, int $expediente, string $que, array $cambios = []) => $this->actingAs($quien, 'api')
        ->postJson("/api/lf-itse/expedientes/{$expediente}/denegar-{$que}/", array_merge([
            'fecha_rechazo' => now()->toDateString(),
            'documento' => 'RES. 045-2026-GDE',
            'observaciones' => 'Reinspección desaprobada: no subsanó lo observado.',
        ], $cambios));
});

it('registra la denegatoria de ITSE y el expediente deja de figurar como pendiente', function () {
    ($this->denegar)($this->itse, $this->expediente, 'itse')
        ->assertCreated()
        ->assertJson(['tipo_autorizacion' => 'ITSE', 'documento' => 'RES. 045-2026-GDE', 'usuario' => $this->itse->id]);

    $pendiente = collect($this->actingAs($this->itse, 'api')->getJson('/api/lf-itse/expedientes/pendientes/')->assertOk()->json())
        ->firstWhere('id', $this->expediente);
    // La licencia todavía espera; la ITSE ya tiene respuesta
    expect($pendiente)->toMatchArray(['itse_pendiente' => false, 'licencia_pendiente' => true]);
});

it('no repite la denegatoria ni deniega lo que el procedimiento no incluye', function () {
    ($this->denegar)($this->itse, $this->expediente, 'itse')->assertCreated();
    ($this->denegar)($this->itse, $this->expediente, 'itse')
        ->assertStatus(422)
        ->assertJson(['error' => 'El expediente N° 501 ya tiene registrada la denegatoria de la ITSE.']);

    ($this->denegar)($this->itse, $this->expSoloLf, 'itse')
        ->assertStatus(422)
        ->assertJson(['error' => 'El procedimiento del expediente N° 502 no incluye la ITSE: no hay nada que denegar.']);

    expect(DB::table('autorizaciones_improcedentes')->count())->toBe(1);
});

it('cada oficina deniega lo suyo y la fecha no puede ser futura', function () {
    ($this->denegar)($this->licencias, $this->expediente, 'itse')->assertForbidden();
    ($this->denegar)($this->itse, $this->expediente, 'licencia')->assertForbidden();
    ($this->denegar)($this->licencias, $this->expediente, 'licencia')->assertCreated()->assertJson(['tipo_autorizacion' => 'LF']);

    ($this->denegar)($this->itse, $this->expediente, 'itse', ['fecha_rechazo' => now()->addDay()->toDateString()])
        ->assertStatus(400)
        ->assertJsonStructure(['fecha_rechazo']);
});
