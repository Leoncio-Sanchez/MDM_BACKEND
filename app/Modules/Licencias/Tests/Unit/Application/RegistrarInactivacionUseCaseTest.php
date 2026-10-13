<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\InactivarLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\RegistrarInactivacionUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\EstadoInactivacionDuplicadoError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->licencias = Mockery::mock(LicenciaRepositoryInterface::class);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $this->useCase = new RegistrarInactivacionUseCase($this->licencias);

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

it('registra una inactivación correctamente', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('existeEstado')->with(1, 5)->once()->andReturn(false);
    $this->licencias->shouldReceive('insertarEstado')
        ->with(1, 5, '2025-01-15', 'DOC-001', 'Inactivación por resolución', 9)
        ->once();

    $dto = new InactivarLicenciaDTO(
        licenciaId: 1,
        estadoId: 5,
        fechaEstado: CarbonImmutable::parse('2025-01-15'),
        documento: 'DOC-001',
        observaciones: 'Inactivación por resolución',
        usuarioId: 9,
    );

    expect($this->useCase->execute($dto))->toBe($this->licenciaMock);
});

it('lanza LicenciaNoEncontrada si no existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);
    $this->licencias->shouldNotReceive('existeEstado');
    $this->licencias->shouldNotReceive('insertarEstado');

    $dto = new InactivarLicenciaDTO(
        licenciaId: 999,
        estadoId: 5,
        fechaEstado: CarbonImmutable::parse('2025-01-15'),
        documento: 'DOC-001',
        observaciones: '',
    );

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(LicenciaNoEncontrada::class);
});

it('lanza EstadoInactivacionDuplicadoError si ya existe el estado', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('existeEstado')->with(1, 5)->once()->andReturn(true);
    $this->licencias->shouldNotReceive('insertarEstado');

    $dto = new InactivarLicenciaDTO(
        licenciaId: 1,
        estadoId: 5,
        fechaEstado: CarbonImmutable::parse('2025-01-15'),
        documento: 'DOC-001',
        observaciones: '',
    );

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(EstadoInactivacionDuplicadoError::class);
});

it('NO modifica el estado de la licencia (solo inserta historial)', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('existeEstado')->with(1, 5)->once()->andReturn(false);
    $this->licencias->shouldReceive('insertarEstado')->once();

    // ⚠️ CRÍTICO: NO debe modificar la licencia (solo inserta historial)
    $this->licencias->shouldNotReceive('actualizar');

    $dto = new InactivarLicenciaDTO(
        licenciaId: 1,
        estadoId: 5,
        fechaEstado: CarbonImmutable::parse('2025-01-15'),
        documento: 'DOC-001',
        observaciones: '',
    );

    $this->useCase->execute($dto);

    expect(true)->toBeTrue();
});
