<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Tests\Unit\Application;

use App\Modules\ITSE\Application\UseCases\ListarEstadosItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Mockery;
use Tests\TestCase;

final class ListarEstadosItseUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_lista_estados_cuando_itse_existe(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);
        $estados = Mockery::mock(ItseEstadoRepositoryInterface::class);

        $itses->shouldReceive('existePorId')
            ->once()
            ->with(1)
            ->andReturn(true);

        $estados->shouldReceive('listarPorItse')
            ->once()
            ->with(1)
            ->andReturn([
                ['id' => 1, 'itse_id' => 1, 'estado_nombre' => 'Vigente'],
            ]);

        $useCase = new ListarEstadosItseUseCase($itses, $estados);
        $resultado = $useCase->execute(1);

        $this->assertCount(1, $resultado);
        $this->assertSame('Vigente', $resultado[0]['estado_nombre']);
    }

    public function test_lanza_excepcion_si_itse_no_existe(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);
        $estados = Mockery::mock(ItseEstadoRepositoryInterface::class);

        $itses->shouldReceive('existePorId')
            ->once()
            ->with(999)
            ->andReturn(false);

        $this->expectException(ItseNoEncontrado::class);

        $useCase = new ListarEstadosItseUseCase($itses, $estados);
        $useCase->execute(999);
    }
}
