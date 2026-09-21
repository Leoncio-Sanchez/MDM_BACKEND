<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Tests\Unit\Application;

use App\Modules\ITSE\Application\UseCases\ListarInspectoresItseUseCase;
use Tests\TestCase;

final class ListarInspectoresItseUseCaseTest extends TestCase
{
    public function test_retorna_array_vacio_siempre_por_td_010(): void
    {
        $useCase = new ListarInspectoresItseUseCase();
        $resultado = $useCase->execute(1);

        $this->assertSame([], $resultado);
    }

    public function test_retorna_array_vacio_para_cualquier_id(): void
    {
        $useCase = new ListarInspectoresItseUseCase();
        $this->assertSame([], $useCase->execute(999));
    }
}
