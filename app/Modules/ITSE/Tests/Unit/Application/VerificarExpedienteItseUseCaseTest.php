<?php
declare(strict_types=1);

use App\Modules\ITSE\Application\DTOs\VerificarExpedienteItseDTO;
use App\Modules\ITSE\Application\UseCases\VerificarExpedienteItseUseCase;
use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\InspeccionReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;

beforeEach(function () {
    $this->expedientes = Mockery::mock(ExpedienteReadRepositoryInterface::class);
    $this->improcedentes = Mockery::mock(AutorizacionImprocedenteReadRepositoryInterface::class);
    $this->itses = Mockery::mock(ItseRepositoryInterface::class);
    $this->inspecciones = Mockery::mock(InspeccionReadRepositoryInterface::class);
    $this->inspecciones->shouldReceive('situacion')->andReturn(null);
    $this->useCase = new VerificarExpedienteItseUseCase($this->expedientes, $this->improcedentes, $this->itses, $this->inspecciones);
});

it('verifica expediente existente', function () {
    $dto = new VerificarExpedienteItseDTO(123, 2026);
    
    $exp = (object)['id' => 1, 'numero_expediente' => 123];
    $this->expedientes->shouldReceive('buscarPorNumeroYAnio')->with(123, 2026)->once()->andReturn($exp);
    $this->improcedentes->shouldReceive('existe')->with(1, 'ITSE')->once()->andReturn(false);
    $this->itses->shouldReceive('buscarPorFiltro')->with('EXPEDIENTE', '123')->once()->andReturn([]);
    
    $result = $this->useCase->execute($dto);
    
    expect($result['se_puede_emitir_itse'])->toBeTrue()
        ->and($result['expediente_id'])->toBe(1);
});

it('retorna existe=false si expediente no existe', function () {
    $dto = new VerificarExpedienteItseDTO(999, 2026);
    
    $this->expedientes->shouldReceive('buscarPorNumeroYAnio')->with(999, 2026)->once()->andReturn(null);
    
    $result = $this->useCase->execute($dto);
    
    expect($result['se_puede_emitir_itse'])->toBeFalse()
        ->and($result['mensaje'])->toBe('El expediente no existe, primero debe ingresarlo.');
});
