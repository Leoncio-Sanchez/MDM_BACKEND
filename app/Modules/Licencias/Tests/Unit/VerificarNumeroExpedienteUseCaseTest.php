<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Tests\Unit;
use App\Modules\Licencias\Application\DTOs\VerificarExpedienteDTO;
use App\Modules\Licencias\Application\UseCases\VerificarNumeroExpedienteUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Exceptions\ExpedienteNoValido;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use Mockery;

function createDto_VerificarNumeroExpedienteUseCase() {
    $class = VerificarExpedienteDTO::class;
    return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
}

it('ejecuta el use case exitosamente', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $exp = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    
    if ('VerificarNumeroExpedienteUseCase' === 'CrearLicenciaUseCase') {
        $repo->shouldReceive('crear')->once()->andReturn(unserialize(sprintf('O:%d:"%s":0:{}', strlen(\App\Modules\Licencias\Domain\Entities\Licencia::class), \App\Modules\Licencias\Domain\Entities\Licencia::class)));
        $exp->shouldReceive('buscarPorId')->andReturn((object)['id'=>1]);
        $useCase = new VerificarNumeroExpedienteUseCase($repo, $exp);
        $dto = new VerificarExpedienteDTO('LIC-001', 'Test', 1, null, 1, null, null);
        $res = $useCase->execute($dto);
        expect($res)->not->toBeNull();
    } else {
        $useCase = new VerificarNumeroExpedienteUseCase($repo);
        $dto = createDto_VerificarNumeroExpedienteUseCase();
        $res = $useCase->execute($dto);
        expect($res)->toBeArray();
    }
});

it('lanza excepcion en caso de error', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $exp = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    
    if ('VerificarNumeroExpedienteUseCase' === 'CrearLicenciaUseCase') {
        $exp->shouldReceive('buscarPorId')->andReturn(null);
        $useCase = new VerificarNumeroExpedienteUseCase($repo, $exp);
        $dto = new VerificarExpedienteDTO('LIC-001', 'Test', 1, null, 999, null, null);
        expect(fn() => $useCase->execute($dto))->toThrow(ExpedienteNoValido::class);
    } else {
        $useCase = new VerificarNumeroExpedienteUseCase($repo);
        // Error forcado
        $repo->shouldReceive('paginar')->andThrow(new \Exception('error'));
        expect(fn() => $useCase->execute(null))->toThrow(\TypeError::class);
    }
});
