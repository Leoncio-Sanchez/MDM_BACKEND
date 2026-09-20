<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Tests\Unit;

use App\Modules\Licencias\Application\UseCases\RegistrarNotificacionUseCase;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

final class RegistrarNotificacionUseCaseTest extends TestCase
{
    public function test_execute(): void
    {
        $repo = Mockery::mock(LicenciaRepositoryInterface::class);
        $useCase = new $uc($repo);
        
        $this->assertNotNull($useCase);
    }
}
