<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\UseCases\EliminarLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaArchivoRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\LicenciaTieneDependientesError;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->licencias = Mockery::mock(LicenciaRepositoryInterface::class);
    $this->archivos = Mockery::mock(LicenciaArchivoRepositoryInterface::class);

    DB::shouldReceive('transaction')->andReturnUsing(fn ($cb) => $cb());

    $this->useCase = new EliminarLicenciaUseCase($this->licencias, $this->archivos);

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

it('elimina una licencia sin dependientes', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('tieneLicenciasDependientes')->with(1)->once()->andReturn(false);
    $this->archivos->shouldReceive('obtenerRutas')->with(1)->once()->andReturn([]);
    $this->licencias->shouldReceive('eliminar')->with(1)->once();

    $this->useCase->execute(1);

    expect(true)->toBeTrue();
});

it('lanza LicenciaNoEncontrada si no existe', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);
    $this->licencias->shouldNotReceive('eliminar');
    $this->archivos->shouldNotReceive('obtenerRutas');

    expect(fn () => $this->useCase->execute(999))
        ->toThrow(LicenciaNoEncontrada::class);
});

it('lanza LicenciaTieneDependientesError si tiene dependientes', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('tieneLicenciasDependientes')->with(1)->once()->andReturn(true);
    $this->licencias->shouldNotReceive('eliminar');
    $this->archivos->shouldNotReceive('obtenerRutas');

    expect(fn () => $this->useCase->execute(1))
        ->toThrow(LicenciaTieneDependientesError::class);
});

it('elimina los archivos físicos después de la transacción', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('tieneLicenciasDependientes')->with(1)->once()->andReturn(false);
    $this->archivos->shouldReceive('obtenerRutas')->with(1)->once()->andReturn(['licencias/archivo1.pdf', 'licencias/archivo2.pdf']);
    $this->licencias->shouldReceive('eliminar')->with(1)->once();

    Storage::shouldReceive('disk')->with('public')->andReturnSelf();
    Storage::shouldReceive('exists')->with('licencias/archivo1.pdf')->once()->andReturn(true);
    Storage::shouldReceive('exists')->with('licencias/archivo2.pdf')->once()->andReturn(true);
    Storage::shouldReceive('delete')->with('licencias/archivo1.pdf')->once();
    Storage::shouldReceive('delete')->with('licencias/archivo2.pdf')->once();

    $this->useCase->execute(1);

    expect(true)->toBeTrue();
});

it('no intenta borrar archivos que no existen físicamente', function () {
    $this->licencias->shouldReceive('buscarPorId')->with(1)->once()->andReturn($this->licenciaMock);
    $this->licencias->shouldReceive('tieneLicenciasDependientes')->with(1)->once()->andReturn(false);
    $this->archivos->shouldReceive('obtenerRutas')->with(1)->once()->andReturn(['licencias/inexistente.pdf']);
    $this->licencias->shouldReceive('eliminar')->with(1)->once();

    Storage::shouldReceive('disk')->with('public')->andReturnSelf();
    Storage::shouldReceive('exists')->with('licencias/inexistente.pdf')->once()->andReturn(false);
    Storage::shouldNotReceive('delete');

    $this->useCase->execute(1);

    expect(true)->toBeTrue();
});
