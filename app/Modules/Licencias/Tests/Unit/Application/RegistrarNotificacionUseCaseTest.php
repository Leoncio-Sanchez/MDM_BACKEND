<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\RegistrarNotificacionDTO;
use App\Modules\Licencias\Application\UseCases\RegistrarNotificacionUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\NotificacionFechaInvalidaError;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->licencias = Mockery::mock(LicenciaRepositoryInterface::class);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $this->useCase = new RegistrarNotificacionUseCase($this->licencias);

    $this->licenciaMock = new Licencia(
        id: 1,
        uuid: 'uuid-123',
        numeroLicencia: NumeroLicencia::fromInt(11111),
        fechaEmision: CarbonImmutable::parse('2025-01-01'),
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

it('registra una notificación con fecha válida', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('actualizarFechaNotificacion')
        ->with(1, '2025-01-15 10:30:00')
        ->once();

    $dto = new RegistrarNotificacionDTO(
        licenciaId: 1,
        fechaNotificacion: CarbonImmutable::parse('2025-01-15 10:30:00'),
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBeInstanceOf(Licencia::class)
        ->and($resultado->fechaNotificacion)->not->toBeNull()
        ->and($resultado->fechaNotificacion->toDateString())->toBe('2025-01-15');
});

it('acepta notificación en la misma fecha que la emisión', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('actualizarFechaNotificacion')
        ->with(1, '2025-01-01 00:00:00')
        ->once();

    $dto = new RegistrarNotificacionDTO(
        licenciaId: 1,
        fechaNotificacion: CarbonImmutable::parse('2025-01-01 00:00:00'),
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBeInstanceOf(Licencia::class);
});

it('lanza LicenciaNoEncontrada si no existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);
    $this->licencias->shouldNotReceive('actualizarFechaNotificacion');

    $dto = new RegistrarNotificacionDTO(
        licenciaId: 999,
        fechaNotificacion: CarbonImmutable::parse('2025-01-15'),
    );

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(LicenciaNoEncontrada::class);
});

it('lanza NotificacionFechaInvalidaError si fecha < emisión', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldNotReceive('actualizarFechaNotificacion');

    $dto = new RegistrarNotificacionDTO(
        licenciaId: 1,
        fechaNotificacion: CarbonImmutable::parse('2024-12-31 23:59:59'),  // anterior a emisión
    );

    expect(fn () => $this->useCase->execute($dto))
        ->toThrow(NotificacionFechaInvalidaError::class);
});
