<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Reportes\Domain\Contracts\GeneradorPdfInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\UsuarioAuth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

const URL_INFORME = '/api/lf-itse/reportes/licencias/pdf/';

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-10-28 10:00:00');
    Carbon::setTestNow('2026-10-28 10:00:00');

    $id = DB::table('auth_user')->insertGetId(UsuarioAuth::datos(['username' => 'p_'.uniqid(), 'is_staff' => true]));
    DB::table('usuarios_perfiles')->insert([
        'expedientes' => false, 'licencias' => true, 'itse' => false, 'admin' => false,
        'user_id' => $id, 'fecha_digitacion' => now(), 'user_id_digitador' => $id,
    ]);
    $this->usuario = User::find($id);

    // Generador falso: guarda lo que recibiría la plantilla (el PDF real se prueba aparte)
    $this->pdf = new class implements GeneradorPdfInterface
    {
        /** @var array<string, mixed> */
        public array $datos = [];

        public function generar(string $plantilla, array $datos, bool $horizontal = false): string
        {
            $this->datos = $datos;

            return '%PDF-falso';
        }
    };
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

it('genera un PDF real y descargable con el número de informe', function () {
    $r = $this->actingAs($this->usuario, 'api')->get(URL_INFORME.'?desde=2026-09-01&hasta=2026-09-30')->assertOk();

    expect($r->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($r->headers->get('Content-Disposition'))->toBe('attachment; filename="Informe_001-2026-MDM-GDES_licencias_2026-09.pdf"')
        ->and($r->headers->get('X-Informe-Codigo'))->toBe('001-2026-MDM/GDES')
        ->and(substr((string) $r->getContent(), 0, 5))->toBe('%PDF-');

    // La reimpresión arma el pie con la fecha original
    $this->actingAs($this->usuario, 'api')->get(URL_INFORME.'?desde=2026-09-01&hasta=2026-09-30')
        ->assertOk()
        ->assertHeader('X-Informe-Reimpresion', '1');
});

it('numera correlativamente y al reimprimir el mismo mes conserva el número', function () {
    $this->app->instance(GeneradorPdfInterface::class, $this->pdf);
    $pedir = fn (string $desde, string $hasta) => $this->actingAs($this->usuario, 'api')
        ->get(URL_INFORME."?desde={$desde}&hasta={$hasta}")->assertOk();

    expect($pedir('2026-08-01', '2026-08-31')->headers->get('X-Informe-Codigo'))->toBe('001-2026-MDM/GDES');
    expect($pedir('2026-09-01', '2026-09-30')->headers->get('X-Informe-Codigo'))->toBe('002-2026-MDM/GDES');

    $reimpresion = $pedir('2026-08-01', '2026-08-31');
    expect($reimpresion->headers->get('X-Informe-Codigo'))->toBe('001-2026-MDM/GDES')
        ->and($reimpresion->headers->get('X-Informe-Reimpresion'))->toBe('1')
        ->and($this->pdf->datos['informe']->esReimpresion)->toBeTrue()
        ->and(DB::table('informes_emitidos')->count())->toBe(2);
});

it('el correlativo vuelve a 001 en un año nuevo', function () {
    $this->app->instance(GeneradorPdfInterface::class, $this->pdf);
    DB::table('informes_emitidos')->insert([
        'tipo' => 'LICENCIAS_MENSUAL', 'anio' => 2025, 'numero' => 12,
        'periodo_desde' => '2025-12-01', 'periodo_hasta' => '2025-12-31',
        'usuario_id' => $this->usuario->id, 'fecha_emision' => now(),
    ]);

    $this->actingAs($this->usuario, 'api')->get(URL_INFORME.'?desde=2026-01-01&hasta=2026-01-31')
        ->assertOk()
        ->assertHeader('X-Informe-Codigo', '001-2026-MDM/GDES');
});

it('usa la configuración del panel en el encabezado y la firma', function () {
    $this->app->instance(GeneradorPdfInterface::class, $this->pdf);
    DB::table('configuracion_sistema')->where('clave', 'informe.nombre_anio')->update(['valor' => 'Año de la Esperanza']);
    DB::table('configuracion_sistema')->where('clave', 'informe.firmante_nombre')->update(['valor' => 'JUAN PÉREZ']);

    $this->actingAs($this->usuario, 'api')->get(URL_INFORME.'?desde=2026-10-01&hasta=2026-10-31')->assertOk();

    expect($this->pdf->datos['config']->nombreAnio)->toBe('Año de la Esperanza')
        ->and($this->pdf->datos['config']->firmanteNombre)->toBe('JUAN PÉREZ')
        ->and($this->pdf->datos['mes'])->toBe('OCTUBRE 2026')
        ->and($this->pdf->datos['fechaEmision'])->toBe('28 de octubre de 2026');
});

it('solo acepta un mes completo que ya empezó', function () {
    $this->app->instance(GeneradorPdfInterface::class, $this->pdf);
    $pedir = fn (string $q) => $this->actingAs($this->usuario, 'api')->getJson(URL_INFORME.$q);

    $pedir('')->assertUnprocessable()->assertJsonValidationErrors(['desde', 'hasta']);
    $pedir('?desde=2026-09-02&hasta=2026-09-30')->assertUnprocessable()->assertJson(['error' => 'El informe formal es mensual: elija un mes completo (del primer al último día).']);
    $pedir('?desde=2026-09-01&hasta=2026-10-31')->assertUnprocessable();
    $pedir('?desde=2026-11-01&hasta=2026-11-30')->assertUnprocessable();

    expect(DB::table('informes_emitidos')->count())->toBe(0); // los rechazos no gastan números
});

it('solo lo emite quien tiene el acceso de Licencias', function () {
    $this->getJson(URL_INFORME.'?desde=2026-09-01&hasta=2026-09-30')->assertUnauthorized();

    DB::table('usuarios_perfiles')->where('user_id', $this->usuario->id)->update(['licencias' => false, 'admin' => true]);
    $this->actingAs($this->usuario, 'api')->getJson(URL_INFORME.'?desde=2026-09-01&hasta=2026-09-30')->assertForbidden();
});

it('la base impide dos informes con el mismo número', function () {
    $fila = fn (string $desde, string $hasta) => [
        'tipo' => 'LICENCIAS_MENSUAL', 'anio' => 2026, 'numero' => 1,
        'periodo_desde' => $desde, 'periodo_hasta' => $hasta,
        'usuario_id' => $this->usuario->id, 'fecha_emision' => now(),
    ];
    DB::table('informes_emitidos')->insert($fila('2026-08-01', '2026-08-31'));

    expect(fn () => DB::table('informes_emitidos')->insert($fila('2026-09-01', '2026-09-30')))
        ->toThrow(UniqueConstraintViolationException::class);
});
