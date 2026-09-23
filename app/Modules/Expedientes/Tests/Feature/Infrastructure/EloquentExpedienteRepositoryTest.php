<?php
declare(strict_types=1);

use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Infrastructure\Persistence\Models\ExpedienteModel;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentExpedienteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentExpedienteRepository();
});

it('crea y busca un expediente por ID', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 101,
        fechaRecepcion: new DateTimeImmutable('2025-03-01 10:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 2,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-03-15'),
        fechaAlerta: new DateTimeImmutable('2025-03-12'),
        representanteId: 3,
        observaciones: 'Expediente test',
    );

    $creado = $this->repo->crear($exp);

    expect($creado->id)->not->toBeNull()
        ->and($creado->numeroExpediente)->toBe(101)
        ->and($creado->solicitanteId)->toBe(2);

    $encontrado = $this->repo->buscarPorId((int) $creado->id);
    expect($encontrado)->not->toBeNull()
        ->and($encontrado->id)->toBe($creado->id)
        ->and($encontrado->numeroExpediente)->toBe(101)
        ->and($encontrado->observaciones)->toBe('Expediente test');
});

it('actualiza un expediente existente', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 200,
        fechaRecepcion: new DateTimeImmutable('2025-04-01 09:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 2,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-04-10'),
        fechaAlerta: new DateTimeImmutable('2025-04-07'),
    );
    $creado = $this->repo->crear($exp);

    $modificado = $creado->actualizar(
        tipoProcedimientoTupaId: 2,
        numeroExpediente: 201,
        fechaRecepcion: new DateTimeImmutable('2025-04-02 09:00:00'),
        solicitanteId: 5,
        representanteId: 6,
        observaciones: 'Obs modificada',
        fechaVencimiento: new DateTimeImmutable('2025-04-16'),
        fechaAlerta: new DateTimeImmutable('2025-04-13'),
    );

    $actualizado = $this->repo->actualizar($modificado);

    expect($actualizado->numeroExpediente)->toBe(201)
        ->and($actualizado->tipoProcedimientoTupaId)->toBe(2)
        ->and($actualizado->solicitanteId)->toBe(5)
        ->and($actualizado->observaciones)->toBe('Obs modificada');
});

it('elimina un expediente por ID', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 300,
        fechaRecepcion: new DateTimeImmutable('2025-05-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-05-15'),
        fechaAlerta: new DateTimeImmutable('2025-05-12'),
    );
    $creado = $this->repo->crear($exp);

    $this->repo->eliminar((int) $creado->id);

    expect($this->repo->buscarPorId((int) $creado->id))->toBeNull();
});

it('lista y pagina expedientes', function () {
    for ($i = 1; $i <= 3; $i++) {
        $exp = Expediente::crear(
            uuid: Uuid::uuid4()->toString(),
            numeroExpediente: 10 + $i,
            fechaRecepcion: new DateTimeImmutable('2025-01-0' . $i),
            tipoProcedimientoTupaId: 1,
            solicitanteId: 1,
            usuarioId: 1,
            fechaVencimiento: new DateTimeImmutable('2025-01-20'),
            fechaAlerta: new DateTimeImmutable('2025-01-18'),
        );
        $this->repo->crear($exp);
    }

    $lista = $this->repo->listar();
    expect($lista)->toHaveCount(3);

    $paginado = $this->repo->paginar(pagina: 1, porPagina: 2);
    expect($paginado['total'])->toBe(3)
        ->and($paginado['items'])->toHaveCount(2);
});

it('verifica existencia de número en año con y sin exceptoId', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 55,
        fechaRecepcion: new DateTimeImmutable('2025-06-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-06-15'),
        fechaAlerta: new DateTimeImmutable('2025-06-12'),
    );
    $creado = $this->repo->crear($exp);

    // Mismo número y año existe
    expect($this->repo->existeNumeroEnAnio(55, 2025))->toBeTrue();
    // Distinto número no existe
    expect($this->repo->existeNumeroEnAnio(56, 2025))->toBeFalse();
    // Distinto año no existe
    expect($this->repo->existeNumeroEnAnio(55, 2026))->toBeFalse();
    // Con exceptoId del mismo expediente retorna false
    expect($this->repo->existeNumeroEnAnio(55, 2025, (int) $creado->id))->toBeFalse();
    // Con exceptoId de otro id retorna true
    expect($this->repo->existeNumeroEnAnio(55, 2025, 9999))->toBeTrue();
});

it('calcula siguiente número del año', function () {
    // Cuando no hay registros para el año retorna 1
    expect($this->repo->siguienteNumeroEnAnio(2027))->toBe(1);

    // Crear expedientes en 2025
    $exp1 = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 41,
        fechaRecepcion: new DateTimeImmutable('2025-07-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-07-15'),
        fechaAlerta: new DateTimeImmutable('2025-07-12'),
    );
    $this->repo->crear($exp1);

    expect($this->repo->siguienteNumeroEnAnio(2025))->toBe(42);

    $exp2 = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 100,
        fechaRecepcion: new DateTimeImmutable('2025-07-02'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-07-15'),
        fechaAlerta: new DateTimeImmutable('2025-07-12'),
    );
    $this->repo->crear($exp2);

    expect($this->repo->siguienteNumeroEnAnio(2025))->toBe(101);
});

it('detecta licencias asociadas', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 88,
        fechaRecepcion: new DateTimeImmutable('2025-08-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-08-15'),
        fechaAlerta: new DateTimeImmutable('2025-08-12'),
    );
    $creado = $this->repo->crear($exp);
    $id = (int) $creado->id;

    expect($this->repo->tieneLicencia($id))->toBeFalse();

    DB::table('licencias_funcionamiento')->insert([
        'uuid' => Uuid::uuid4()->toString(),
        'expediente_id' => $id,
        'numero_licencia' => 1234,
        'fecha_emision' => '2025-08-01',
        'nombre_comercial' => 'Test',
        'titular_id' => 1,
        'conductor_id' => 1,
        'tipo_licencia_id' => 1,
        'nivel_riesgo_id' => 1,
        'zonificacion_id' => 1,
        'actividad' => 'Comercio',
        'direccion' => 'Av Test 123',
        'hora_desde' => 8,
        'hora_hasta' => 18,
        'resolucion_numero' => 'RES-100',
        'area' => 50.0,
        'numero_recibo_pago' => 'REC-1234',
        'estado' => 'EMITIDA',
    ]);

    expect($this->repo->tieneLicencia($id))->toBeTrue();
});

it('detecta ITSE asociadas', function () {
    $exp = Expediente::crear(
        uuid: Uuid::uuid4()->toString(),
        numeroExpediente: 89,
        fechaRecepcion: new DateTimeImmutable('2025-08-01'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-08-15'),
        fechaAlerta: new DateTimeImmutable('2025-08-12'),
    );
    $creado = $this->repo->crear($exp);
    $id = (int) $creado->id;

    expect($this->repo->tieneItse($id))->toBeFalse();

    DB::table('itse')->insert([
        'uuid' => Uuid::uuid4()->toString(),
        'expediente_id' => $id,
        'numero_itse' => 9876,
        'fecha_expedicion' => '2025-08-02',
        'fecha_caducidad' => '2027-08-02',
        'nombre_comercial' => 'Test ITSE',
        'direccion' => 'Dir test',
        'resolucion_numero' => 'Res 1',
        'area' => 50.0,
        'numero_recibo_pago' => 'Recibo 1',
        'tipo_itse_id' => 1,
        'usuario_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ]);

    expect($this->repo->tieneItse($id))->toBeTrue();
});

it('ejecuta listarPendientes y buscarPorFiltro y consultar', function () {
    expect($this->repo->buscarPorFiltro('NUMERO', '10'))->toBeArray()
        ->and($this->repo->listarPendientes())->toBeArray()
        ->and($this->repo->consultar(null))->toBeArray();
});

it('lanza excepcion en buscarPorFiltro con filtro no valido', function () {
    expect(fn () => $this->repo->buscarPorFiltro('INVALIDO', '10'))
        ->toThrow(\InvalidArgumentException::class);
});

it('consulta expedientes hidratados con o sin filtro de numero', function () {
    $exp = Expediente::crear(
        uuid: 'test-uuid-999',
        numeroExpediente: 888,
        fechaRecepcion: new DateTimeImmutable('2025-01-03 10:00:00'),
        tipoProcedimientoTupaId: 1,
        solicitanteId: 1,
        usuarioId: 1,
        fechaVencimiento: new DateTimeImmutable('2025-01-10'),
        fechaAlerta: new DateTimeImmutable('2025-01-08'),
    );
    $this->repo->crear($exp);

    $todos = $this->repo->consultar(null);
    expect($todos)->toBeArray()
        ->and(count($todos))->toBeGreaterThanOrEqual(1)
        ->and($todos[0])->toBeInstanceOf(Expediente::class);

    $filtrado = $this->repo->consultar(888);
    expect($filtrado)->toHaveCount(1)
        ->and($filtrado[0]->numeroExpediente)->toBe(888);

    $inexistente = $this->repo->consultar(999999);
    expect($inexistente)->toBeEmpty();
});
