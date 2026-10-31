<?php
declare(strict_types=1);

use App\Modules\Licencias\Application\DTOs\CrearLicenciaDTO;
use App\Modules\Licencias\Application\UseCases\CrearLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDenegadaError;
use App\Modules\Licencias\Domain\Exceptions\LicenciaDuplicadaError;

/** Payload completo tal como lo envía el formulario (lo que deja pasar CrearLicenciaRequest). */
function payloadLicencia(array $cambios = []): array
{
    return [
        'usuario_id' => 1,
        'numero_licencia' => '1001',
        'nombre_comercial' => 'Restaurante El Buen Sabor',
        'tipo_licencia_id' => 1,
        'titular_id' => 7,
        'conductor_id' => 8,
        'expediente_id' => 1,
        'nivel_riesgo_id' => 2,
        'zonificacion_id' => 3,
        'actividad' => 'RESTAURANTE',
        'direccion' => 'AV. GRAU 450',
        'hora_desde' => 8,
        'hora_hasta' => 23,
        'resolucion_numero' => 'RES-045-2026',
        'area' => 120.5,
        'numero_recibo_pago' => 'REC-001',
        'es_vigencia_indeterminada' => true,
        ...$cambios,
    ];
}

it('crea la licencia con todos los datos del formulario', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')->with(1)->once()->andReturn((object) ['id' => 1]);
    $expedienteRepo->shouldReceive('estaAnulado')->with(1)->andReturn(false);
    $expedienteRepo->shouldReceive('tieneAutorizacionImprocedente')->with(1, 'LF')->andReturn(false);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldReceive('existeNumeroLicencia')->with(1001)->once()->andReturn(false);
    $licenciaRepo->shouldReceive('existeReciboPago')->with('REC-001')->once()->andReturn(false);
    $licenciaRepo->shouldReceive('crear')->once()->andReturnArg(0);

    $resultado = (new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo))
        ->execute(CrearLicenciaDTO::fromArray(payloadLicencia()));

    expect($resultado)->toBeInstanceOf(Licencia::class)
        ->and($resultado->numeroLicencia->toInt())->toBe(1001)
        ->and($resultado->nombreComercial->value)->toBe('Restaurante El Buen Sabor')
        ->and($resultado->titularId)->toBe(7)
        ->and($resultado->conductorId)->toBe(8)
        ->and($resultado->actividad)->toBe('RESTAURANTE')
        ->and($resultado->direccion)->toBe('AV. GRAU 450')
        ->and($resultado->horaDesde)->toBe(8)
        ->and($resultado->horaHasta)->toBe(23)
        ->and($resultado->resolucionNumero)->toBe('RES-045-2026')
        ->and($resultado->area)->toBe(120.5)
        ->and($resultado->numeroReciboPago)->toBe('REC-001');
});

it('lanza ExpedienteNoValido si el expediente no existe', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')->with(999)->once()->andReturn(null);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldNotReceive('crear');

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    expect(fn () => $useCase->execute(CrearLicenciaDTO::fromArray(payloadLicencia(['expediente_id' => 999]))))
        ->toThrow(ExpedienteNoValido::class);
});

it('usa al titular como conductor si no se indica', function () {
    $payload = payloadLicencia();
    unset($payload['conductor_id']);

    expect(CrearLicenciaDTO::fromArray($payload)->conductorId)->toBe(7);
});

it('lanza LicenciaDuplicadaError si el número ya existe y no intenta insertar', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')->andReturn((object) ['id' => 1]);
    $expedienteRepo->shouldReceive('estaAnulado')->andReturn(false);
    $expedienteRepo->shouldReceive('tieneAutorizacionImprocedente')->andReturn(false);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldReceive('existeNumeroLicencia')->with(1001)->once()->andReturn(true);
    $licenciaRepo->shouldNotReceive('crear');

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    expect(fn () => $useCase->execute(CrearLicenciaDTO::fromArray(payloadLicencia())))
        ->toThrow(LicenciaDuplicadaError::class);
});

it('rechaza la licencia si el expediente tiene la denegatoria de licencia y no la crea', function () {
    $expedienteRepo = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $expedienteRepo->shouldReceive('buscarPorId')->with(1)->andReturn((object) ['id' => 1]);
    $expedienteRepo->shouldReceive('estaAnulado')->with(1)->andReturn(false);
    $expedienteRepo->shouldReceive('tieneAutorizacionImprocedente')->with(1, 'LF')->once()->andReturn(true);

    $licenciaRepo = Mockery::mock(LicenciaRepositoryInterface::class);
    $licenciaRepo->shouldNotReceive('crear');

    $useCase = new CrearLicenciaUseCase($licenciaRepo, $expedienteRepo);

    expect(fn () => $useCase->execute(CrearLicenciaDTO::fromArray(payloadLicencia())))
        ->toThrow(LicenciaDenegadaError::class, 'denegatoria de la licencia');
});
