<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\ModificarLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\ModificarLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaGiroRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDuplicadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\ReciboPagoDuplicadoError;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->licencias = Mockery::mock(LicenciaRepositoryInterface::class);
    $this->giros = Mockery::mock(LicenciaGiroRepositoryInterface::class);
    $this->expedientes = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    
    DB::shouldReceive('transaction')
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->useCase = new ModificarLicenciaUseCase(
        $this->licencias,
        $this->giros,
        $this->expedientes,
    );

    $this->licenciaMock = new Licencia(
        id: 1,
        uuid: 'uuid-123',
        numeroLicencia: NumeroLicencia::fromInt(11111),
        fechaEmision: CarbonImmutable::now(),
        nombreComercial: NombreComercial::fromString('Antiguo'),
        titularId: 10,
        conductorId: 20,
        licenciaPrincipalId: null,
        expedienteId: 30,
        tipoLicenciaId: 1,
        nivelRiesgoId: 2,
        zonificacionId: 3,
        esVigenciaIndeterminada: true,
        fechaInicioVigencia: null,
        fechaFinVigencia: null,
        actividad: '',
        direccion: '',
        horaDesde: 9,
        horaHasta: 18,
        resolucionNumero: '',
        area: 100.0,
        numeroReciboPago: '',
        observaciones: null,
        sePuedePublicar: false,
        fechaNotificacion: null,
        estado: EstadoLicencia::registrada()
    );
});

afterEach(function () {
    Mockery::close();
});

it('modifica una licencia correctamente y actualiza giros', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    
    $this->expedientes->shouldReceive('tieneAutorizacionImprocedente')
        ->with(30, 'LF')->once()->andReturn(false);

    $this->licencias->shouldReceive('existeNumeroLicencia')->with(12345, 1)->once()->andReturn(false);
    $this->licencias->shouldReceive('existeReciboPago')->with('REC-001', 1)->once()->andReturn(false);
    
    $this->licencias->shouldReceive('actualizar')->once()->andReturnUsing(fn ($l) => $l);

    $this->giros->shouldReceive('eliminarGiros')->with(1)->once();
    $this->giros->shouldReceive('insertarGiros')->with(1, [5, 6], 9)->once();

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9,
        numeroLicencia: 12345,
        nombreComercial: 'Nuevo Nombre',
        tipoLicenciaId: 1,
        titularId: 10,
        conductorId: 20,
        expedienteId: 30,
        nivelRiesgoId: 2,
        zonificacionId: 3,
        numeroReciboPago: 'REC-001',
        giros: [5, 6],
    );

    $resultado = $this->useCase->execute($dto);

    expect($resultado)->toBeInstanceOf(Licencia::class)
        ->and($resultado->numeroLicencia->value)->toBe(12345)
        ->and($resultado->nombreComercial->value)->toBe('Nuevo Nombre');
});

it('lanza LicenciaNoEncontrada si no existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturnNull();

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9, numeroLicencia: 12345, nombreComercial: 'Test',
        tipoLicenciaId: 1, titularId: 1, conductorId: 1, expedienteId: 1, nivelRiesgoId: 1, zonificacionId: 1
    );

    expect(fn () => $this->useCase->execute($dto))->toThrow(LicenciaNoEncontrada::class);
});

it('lanza LicenciaDenegadaError si expediente tiene autorización improcedente', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    
    $this->expedientes->shouldReceive('tieneAutorizacionImprocedente')
        ->with(30, 'LF')->once()->andReturn(true);

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9, numeroLicencia: 12345, nombreComercial: 'Test',
        tipoLicenciaId: 1, titularId: 1, conductorId: 1, expedienteId: 30, nivelRiesgoId: 1, zonificacionId: 1
    );

    expect(fn () => $this->useCase->execute($dto))->toThrow(LicenciaDenegadaError::class);
});

it('lanza LicenciaDuplicadaError si número existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->expedientes->shouldReceive('tieneAutorizacionImprocedente')->andReturn(false);
    $this->licencias->shouldReceive('existeNumeroLicencia')->with(12345, 1)->once()->andReturn(true);

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9, numeroLicencia: 12345, nombreComercial: 'Test',
        tipoLicenciaId: 1, titularId: 1, conductorId: 1, expedienteId: 30, nivelRiesgoId: 1, zonificacionId: 1
    );

    expect(fn () => $this->useCase->execute($dto))->toThrow(LicenciaDuplicadaError::class);
});

it('lanza ReciboPagoDuplicadoError si recibo existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->expedientes->shouldReceive('tieneAutorizacionImprocedente')->andReturn(false);
    $this->licencias->shouldReceive('existeNumeroLicencia')->andReturn(false);
    $this->licencias->shouldReceive('existeReciboPago')->with('REC-001', 1)->once()->andReturn(true);

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9, numeroLicencia: 12345, nombreComercial: 'Test',
        tipoLicenciaId: 1, titularId: 1, conductorId: 1, expedienteId: 30, nivelRiesgoId: 1, zonificacionId: 1,
        numeroReciboPago: 'REC-001'
    );

    expect(fn () => $this->useCase->execute($dto))->toThrow(ReciboPagoDuplicadoError::class);
});

it('fuerza fechas null si es_vigencia_indeterminada', function () {
    $this->licencias->shouldReceive('buscarPorId')->once()->andReturn($this->licenciaMock);
    $this->expedientes->shouldReceive('tieneAutorizacionImprocedente')->once()->andReturn(false);
    $this->licencias->shouldReceive('existeNumeroLicencia')->once()->andReturn(false);
    $this->licencias->shouldReceive('actualizar')->once()->andReturnUsing(fn ($l) => $l);
    $this->giros->shouldReceive('eliminarGiros')->once();

    $dto = new ModificarLicenciaDTO(
        licenciaId: 1,
        usuarioId: 9, numeroLicencia: 12345, nombreComercial: 'Test',
        tipoLicenciaId: 1, titularId: 1, conductorId: 1, expedienteId: 30, nivelRiesgoId: 1, zonificacionId: 1,
        esVigenciaIndeterminada: true,
        fechaInicioVigencia: \Carbon\CarbonImmutable::now(),
        fechaFinVigencia: \Carbon\CarbonImmutable::now()->addYear()
    );

    $resultado = $this->useCase->execute($dto);
    
    expect($resultado->esVigenciaIndeterminada)->toBeTrue()
        ->and($resultado->fechaInicioVigencia)->toBeNull()
        ->and($resultado->fechaFinVigencia)->toBeNull();
});
