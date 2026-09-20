<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Tests\Unit;
use App\Modules\Licencias\Application\DTOs\ConsultaLicenciasDTO;
use App\Modules\Licencias\Application\UseCases\ConsultaLicenciasUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use Mockery;

it('ejecuta el use case exitosamente', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->andReturn(['items' => [], 'total' => 0]);
    $useCase = new ConsultaLicenciasUseCase($repo);
    $dto = new ConsultaLicenciasDTO(null);
    $res = $useCase->execute($dto);
    expect($res)->toBeArray();
});

it('lanza excepcion en caso de error', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('paginar')->andThrow(new \Exception('error'));
    $useCase = new ConsultaLicenciasUseCase($repo);
    $dto = new ConsultaLicenciasDTO(null);
    expect(fn() => $useCase->execute($dto))->toThrow(\Exception::class);
});
