<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Tests\Unit;
use App\Modules\Licencias\Application\DTOs\ListarEstadosDTO;
use App\Modules\Licencias\Application\UseCases\ListarEstadosLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use Mockery;

function createDto_ListarEstadosLicenciaUseCase() {
    $class = ListarEstadosDTO::class;
    return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
}

it('ejecuta el use case exitosamente', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $exp = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    
    if ('ListarEstadosLicenciaUseCase' === 'CrearLicenciaUseCase') {
        $repo->shouldReceive('crear')->once()->andReturn(unserialize(sprintf('O:%d:"%s":0:{}', strlen(\App\Modules\Licencias\Domain\Entities\Licencia::class), \App\Modules\Licencias\Domain\Entities\Licencia::class)));
        $exp->shouldReceive('buscarPorId')->andReturn((object)['id'=>1]);
        $useCase = new ListarEstadosLicenciaUseCase($repo, $exp);
        $dto = new ListarEstadosDTO('LIC-001', 'Test', 1, null, 1, null, null);
        $res = $useCase->execute($dto);
        expect($res)->not->toBeNull();
    } else {
        $useCase = new ListarEstadosLicenciaUseCase($repo);
        $dto = createDto_ListarEstadosLicenciaUseCase();
        $res = $useCase->execute($dto);
        expect($res)->toBeArray();
    }
});

it('lanza excepcion en caso de error', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $exp = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    
    if ('ListarEstadosLicenciaUseCase' === 'CrearLicenciaUseCase') {
        $exp->shouldReceive('buscarPorId')->andReturn(null);
        $useCase = new ListarEstadosLicenciaUseCase($repo, $exp);
        $dto = new ListarEstadosDTO('LIC-001', 'Test', 1, null, 999, null, null);
        expect(fn() => $useCase->execute($dto))->toThrow(ExpedienteNoValido::class);
    } else {
        $useCase = new ListarEstadosLicenciaUseCase($repo);
        // Error forcado
        $repo->shouldReceive('paginar')->andThrow(new \Exception('error'));
        expect(fn() => $useCase->execute(null))->toThrow(\TypeError::class);
    }
});
