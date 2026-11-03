<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\ITSE\Application\DTOs\CrearItseDTO;
use App\Modules\ITSE\Application\UseCases\CrearItseUseCase;
use App\Modules\ITSE\Domain\Exceptions\ExpedienteNoValidoItse;
use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\CrearLicenciaUseCase;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError;
use App\Shared\Domain\Calendario;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Registros;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * La denegatoria no se deshace: una vez registrada, ese expediente ya no admite
 * la licencia o el certificado ITSE que se denegó.
 */
beforeEach(function () {
    DB::table('auth_user')->insert(UsuarioAuth::datos(['id' => 1, 'username' => 'admin', 'is_staff' => true, 'is_superuser' => true]));
    $this->user = User::find(1);

    $tupa = DB::table('tipos_procedimiento_tupa')->insertGetId(Registros::tipoProcedimientoTupa([
        'requiere_lf' => true, 'requiere_itse' => true, 'plazo_atencion_dias' => 10, 'dias_alerta_vencimiento' => 3,
    ]));
    $persona = DB::table('personas')->insertGetId(Registros::persona(['nombres' => 'COMERCIAL SELVA', 'tipo_persona' => 'J']));
    $this->expediente = DB::table('expedientes')->insertGetId(Registros::expediente([
        'numero_expediente' => 601, 'solicitante_id' => $persona, 'tipo_procedimiento_tupa_id' => $tupa,
    ]));
});

$denegar = function (string $que) {
    $this->actingAs($this->user, 'api')
        ->postJson("/api/lf-itse/expedientes/{$this->expediente}/denegar-{$que}/", [
            'fecha_rechazo' => Calendario::hoy()->toDateString(), 'documento' => 'RG 015-2026', 'observaciones' => 'No subsanó',
        ])
        ->assertCreated();
};

it('no permite emitir la licencia después de denegarla', function () use ($denegar) {
    $denegar->call($this, 'licencia');

    $datos = [
        'usuario_id' => 1, 'numero_licencia' => 900, 'nombre_comercial' => 'BODEGA', 'tipo_licencia_id' => 1,
        'titular_id' => 1, 'expediente_id' => $this->expediente, 'nivel_riesgo_id' => 1, 'zonificacion_id' => 1,
    ];

    expect(fn () => app(CrearLicenciaUseCase::class)->execute(CrearLicenciaDTO::fromArray($datos)))
        ->toThrow(LicenciaDenegadaError::class, 'denegatoria de la licencia');
    expect(DB::table('licencias_funcionamiento')->count())->toBe(0);
});

it('no permite emitir el certificado ITSE después de denegarlo', function () use ($denegar) {
    $denegar->call($this, 'itse');

    $dto = new CrearItseDTO(
        numeroItse: 900, fechaExpedicion: CarbonImmutable::now(), fechaSolicitudRenovacion: null,
        fechaCaducidad: CarbonImmutable::now()->addYears(2), nombreComercial: 'BODEGA', direccion: 'JR. LIMA 1',
        resolucionNumero: 'RES-1', area: 50, capacidadAforo: 20, numeroReciboPago: 'REC-900', observaciones: null,
        sePuedePublicar: false, tipoItseId: 1, expedienteId: $this->expediente, itsePrincipalId: null,
        usuarioId: 1, nivelRiesgoId: 1, conductorId: 1, titularId: 1,
    );

    expect(fn () => app(CrearItseUseCase::class)->execute($dto))
        ->toThrow(ExpedienteNoValidoItse::class, 'denegatoria de ITSE');
    expect(DB::table('itse')->count())->toBe(0);
});
