<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Tests\Unit\Application;

use App\Modules\ITSE\Application\DTOs\PorRenovarItseDTO;
use App\Modules\ITSE\Application\UseCases\ListarPorRenovarItseUseCase;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

final class ListarPorRenovarItseUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_lista_por_renovar_con_filtros(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);

        $mockItse = new Itse(
            id: 1,
            uuid: 'uuid-1',
            numeroItse: 1001,
            fechaExpedicion: CarbonImmutable::parse('2026-01-01'),
            fechaSolicitudRenovacion: CarbonImmutable::parse('2026-02-01'),
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

        $itses->shouldReceive('listarPorRenovar')
            ->once()
            ->with('2026-01-01', '2026-03-01')
            ->andReturn([$mockItse]);

        $useCase = new ListarPorRenovarItseUseCase($itses);
        $dto = new PorRenovarItseDTO(fechaDesde: '2026-01-01', fechaHasta: '2026-03-01');
        $resultado = $useCase->execute($dto);

        $this->assertCount(1, $resultado);
        $this->assertSame(1001, $resultado[0]->numeroItse);
    }

    public function test_lista_por_renovar_sin_filtros(): void
    {
        $itses = Mockery::mock(ItseRepositoryInterface::class);

        $itses->shouldReceive('listarPorRenovar')
            ->once()
            ->with(null, null)
            ->andReturn([]);

        $useCase = new ListarPorRenovarItseUseCase($itses);
        $dto = new PorRenovarItseDTO(fechaDesde: null, fechaHasta: null);
        $resultado = $useCase->execute($dto);

        $this->assertSame([], $resultado);
    }
}
