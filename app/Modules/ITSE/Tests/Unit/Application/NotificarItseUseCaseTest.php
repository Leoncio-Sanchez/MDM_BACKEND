<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\NotificarItseDTO;
use App\Modules\ITSE\Application\UseCases\NotificarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseFechaInvalida;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
        return $callback();
    });
});

it('notifica un ITSE con fecha válida', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $dto = new NotificarItseDTO(1, CarbonImmutable::now());
    
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now()->subDay(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($itse);
    $repo->shouldReceive('actualizar')->once()->andReturnUsing(fn(Itse $i) => $i);
    
    $useCase = new NotificarItseUseCase($repo);
    $result = $useCase->execute($dto);
    
    expect($result->fechaNotificacion)->not->toBeNull();
});

it('lanza ItseFechaInvalida si fecha < expedición', function () {
    $repo = Mockery::mock(ItseRepositoryInterface::class);
    $dto = new NotificarItseDTO(1, CarbonImmutable::now()->subDays(2));
    
    $itse = Itse::crear('uuid', 123, CarbonImmutable::now()->subDay(), null, CarbonImmutable::now()->addYear(), 'A', 'B', 'C', 10.5, 100, 'R123', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
    
    $repo->shouldReceive('buscarPorId')->with(1)->once()->andReturn($itse);
    
    $useCase = new NotificarItseUseCase($repo);
    $useCase->execute($dto);
})->throws(ItseFechaInvalida::class);
