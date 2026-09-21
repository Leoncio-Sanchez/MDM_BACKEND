<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Tests\Unit\Application;

use App\Modules\ITSE\Application\DTOs\ConsultaItseDTO;
use App\Modules\ITSE\Application\UseCases\ConsultaItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

final class ConsultaItseUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_consulta_con_numero_itse(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);

        $mockItse = new Itse(
            id: 1,
            uuid: 'uuid-1',
            numeroItse: 999999,
            fechaExpedicion: CarbonImmutable::parse('2026-01-01'),
            fechaSolicitudRenovacion: null,
            fechaCaducidad: CarbonImmutable::parse('2027-01-01'),
            nombreComercial: 'Test',
            direccion: 'Lima',
            resolucionNumero: 'R-01',
            area: 50.0,
            capacidadAforo: 10,
            numeroReciboPago: 'REC-01',
            observaciones: null,
            sePuedePublicar: true,
            fechaNotificacion: null,
            fechaDigitacion: CarbonImmutable::parse('2026-01-01 10:00:00'),
            tipoItseId: 1,
            expedienteId: 10,
            itsePrincipalId: null,
            usuarioId: 1,
            nivelRiesgoId: 1,
            conductorId: 1,
            titularId: 1,
        );

        $itses->shouldReceive('consultaRapida')
            ->once()
            ->with(999999)
            ->andReturn([$mockItse]);

        $useCase = new ConsultaItseUseCase($itses);
        $dto = new ConsultaItseDTO(numeroItse: 999999);
        $resultado = $useCase->execute($dto);

        $this->assertCount(1, $resultado);
        $this->assertSame(999999, $resultado[0]->numeroItse);
    }

    public function test_consulta_sin_filtros(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);

        $itses->shouldReceive('consultaRapida')
            ->once()
            ->with(null)
            ->andReturn([]);

        $useCase = new ConsultaItseUseCase($itses);
        $dto = new ConsultaItseDTO(numeroItse: null);
        $resultado = $useCase->execute($dto);

        $this->assertSame([], $resultado);
    }
}
