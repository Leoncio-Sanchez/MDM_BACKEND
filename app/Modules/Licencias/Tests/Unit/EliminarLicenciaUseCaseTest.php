<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Tests\Unit;
use App\Modules\Licencias\Application\UseCases\EliminarLicenciaUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use Mockery;

it('ejecuta el use case exitosamente', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('eliminar')->once()->with(1);
    $useCase = new EliminarLicenciaUseCase($repo);
    $useCase->execute(1);
    expect(true)->toBeTrue();
});

it('lanza excepcion en caso de error', function () {
    $repo = Mockery::mock(LicenciaRepositoryInterface::class);
    $repo->shouldReceive('eliminar')->andThrow(new \Exception('Error'));
    $useCase = new EliminarLicenciaUseCase($repo);
    expect(fn() => $useCase->execute(1))->toThrow(\Exception::class);
});
