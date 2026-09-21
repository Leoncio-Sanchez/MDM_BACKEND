<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Tests\Unit\Application;

use App\Modules\ITSE\Application\UseCases\ListarGirosItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseGiroRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Mockery;
use Tests\TestCase;

final class ListarGirosItseUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_lista_giros_cuando_itse_existe(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);
        $giros = Mockery::mock(ItseGiroRepositoryInterface::class);

        $itses->shouldReceive('existePorId')
            ->once()
            ->with(1)
            ->andReturn(true);

        $giros->shouldReceive('listarPorItse')
            ->once()
            ->with(1)
            ->andReturn([
                ['id' => 10, 'itse_id' => 1, 'giro_id' => 2, 'ciiu_id' => 111, 'nombre' => 'COMERCIO'],
            ]);

        $useCase = new ListarGirosItseUseCase($itses, $giros);
        $resultado = $useCase->execute(1);

        $this->assertCount(1, $resultado);
        $this->assertSame('COMERCIO', $resultado[0]['nombre']);
    }

    public function test_lanza_excepcion_si_itse_no_existe(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);
        $giros = Mockery::mock(ItseGiroRepositoryInterface::class);

        $itses->shouldReceive('existePorId')
            ->once()
            ->with(999)
            ->andReturn(false);

        $this->expectException(ItseNoEncontrado::class);

        $useCase = new ListarGirosItseUseCase($itses, $giros);
        $useCase->execute(999);
    }
}
