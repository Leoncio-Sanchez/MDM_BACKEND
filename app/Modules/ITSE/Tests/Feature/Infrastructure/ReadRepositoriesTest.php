<?php
declare(strict_types=1);

use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentExpedienteReadRepository;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentPersonaReadRepository;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentNivelRiesgoReadRepository;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentZonificacionReadRepository;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentAutorizacionImprocedenteReadRepository;

uses(Tests\TestCase::class);

it('Repositorios instancian', function () {
    expect(new EloquentExpedienteReadRepository())->not->toBeNull();
    expect(new EloquentPersonaReadRepository())->not->toBeNull();
    expect(new EloquentNivelRiesgoReadRepository())->not->toBeNull();
    expect(new EloquentZonificacionReadRepository())->not->toBeNull();
    expect(new EloquentAutorizacionImprocedenteReadRepository())->not->toBeNull();
});
