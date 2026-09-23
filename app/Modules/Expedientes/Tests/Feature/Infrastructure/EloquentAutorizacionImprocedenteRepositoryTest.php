<?php

declare(strict_types=1);

use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentAutorizacionImprocedenteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentAutorizacionImprocedenteRepository;
});

it('crea y busca una autorizacion improcedente por expediente y tipo', function () {
    $auto = AutorizacionImprocedente::crear(
        expedienteId: 10,
        tipo: TipoAutorizacion::LF,
        documento: 'DOC-123.pdf',
        observaciones: 'Rechazo por falta de requisitos',
        usuarioId: 1,
    );

    $creada = $this->repo->crear($auto);

    expect($creada->id)->not->toBeNull()
        ->and($creada->expedienteId)->toBe(10)
        ->and($creada->tipoAutorizacion)->toBe(TipoAutorizacion::LF)
        ->and($creada->documento)->toBe('DOC-123.pdf');

    $encontrada = $this->repo->buscarPorExpedienteYTipo(10, TipoAutorizacion::LF);
    expect($encontrada)->not->toBeNull()
        ->and($encontrada->expedienteId)->toBe(10)
        ->and($encontrada->tipoAutorizacion)->toBe(TipoAutorizacion::LF);

    // Tipo distinto no encontrada
    expect($this->repo->buscarPorExpedienteYTipo(10, TipoAutorizacion::ITSE))->toBeNull();
});

it('verifica existencia de autorizacion improcedente', function () {
    expect($this->repo->existe(20, TipoAutorizacion::ITSE))->toBeFalse();

    $auto = AutorizacionImprocedente::crear(
        expedienteId: 20,
        tipo: TipoAutorizacion::ITSE,
        documento: null,
        observaciones: null,
        usuarioId: 1,
    );
    $this->repo->crear($auto);

    expect($this->repo->existe(20, TipoAutorizacion::ITSE))->toBeTrue()
        ->and($this->repo->existe(20, TipoAutorizacion::LF))->toBeFalse();
});
