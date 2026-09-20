<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\ListarEstadosDTO;
use App\Modules\Licencias\Application\UseCases\ListarEstadosLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->licencias = Mockery::mock(LicenciaRepositoryInterface::class);

    $this->useCase = new ListarEstadosLicenciaUseCase($this->licencias);

    $this->licenciaMock = new Licencia(
        id: 1,
        uuid: 'uuid-123',
        numeroLicencia: NumeroLicencia::fromInt(11111),
        fechaEmision: CarbonImmutable::now(),
        nombreComercial: NombreComercial::fromString('Test'),
        titularId: 1,
        conductorId: 2,
        licenciaPrincipalId: null,
        expedienteId: 1,
        tipoLicenciaId: 1,
        nivelRiesgoId: 1,
        zonificacionId: 1,
        esVigenciaIndeterminada: true,
        fechaInicioVigencia: null,
        fechaFinVigencia: null,
        actividad: 'Test',
        direccion: 'Test',
        horaDesde: 9,
        horaHasta: 18,
        resolucionNumero: 'RES-001',
        area: 100.0,
        numeroReciboPago: 'REC-001',
        observaciones: null,
        sePuedePublicar: false,
        fechaNotificacion: null,
        estado: EstadoLicencia::registrada(),
    );
});

afterEach(function () {
    Mockery::close();
});

it('lista los estados de una licencia con historial', function () {
    $estados = [
        [
            'id' => 1,
            'licencia_funcionamiento_id' => 1,
            'estado_id' => 1,
            'fecha_estado' => '2025-01-01',
            'documento' => 'DOC-001',
            'observaciones' => 'Registro inicial',
            'usuario_id' => 1,
            'fecha_digitacion' => '2025-01-01 10:00:00',
            'estado_nombre' => 'REGISTRADA',
            'es_para_lf' => true,
            'es_para_itse' => true,
            'esta_activo' => true,
        ],
        [
            'id' => 2,
            'licencia_funcionamiento_id' => 1,
            'estado_id' => 2,
            'fecha_estado' => '2025-01-15',
            'documento' => 'DOC-002',
            'observaciones' => 'Activación',
            'usuario_id' => 1,
            'fecha_digitacion' => '2025-01-15 11:00:00',
            'estado_nombre' => 'ACTIVA',
            'es_para_lf' => true,
            'es_para_itse' => false,
            'esta_activo' => true,
        ],
    ];

    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('listarEstados')->with(1)->once()->andReturn($estados);

    $resultado = $this->useCase->execute(new ListarEstadosDTO(licenciaId: 1));

    expect($resultado)
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resultado[0]['estado_nombre'])->toBe('REGISTRADA')
        ->and($resultado[1]['estado_nombre'])->toBe('ACTIVA')
        ->and($resultado[0]['fecha_estado'])->toBe('2025-01-01');
});

it('retorna array vacío si la licencia no tiene historial', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('listarEstados')->with(1)->once()->andReturn([]);

    $resultado = $this->useCase->execute(new ListarEstadosDTO(licenciaId: 1));

    expect($resultado)->toBeArray()->toBeEmpty();
});

it('lanza LicenciaNoEncontrada si la licencia no existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);
    $this->licencias->shouldNotReceive('listarEstados');

    expect(fn () => $this->useCase->execute(new ListarEstadosDTO(licenciaId: 999)))
        ->toThrow(LicenciaNoEncontrada::class);
});
