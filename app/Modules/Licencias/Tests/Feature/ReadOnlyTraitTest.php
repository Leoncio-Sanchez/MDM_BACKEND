<?php
declare(strict_types=1);
use App\Modules\Licencias\Infrastructure\Persistence\Models\TipoLicenciaReadModel;

it('lanza excepción al crear un ReadModel', function () {
    expect(fn () => TipoLicenciaReadModel::create(['nombre' => 'Test']))
        ->toThrow(RuntimeException::class);
});

it('lanza excepción al actualizar un ReadModel', function () {
    $tipo = new TipoLicenciaReadModel();
    $tipo->exists = true;
    expect(fn () => $tipo->update(['nombre' => 'Otro']))
        ->toThrow(RuntimeException::class);
});

it('lanza excepción al eliminar un ReadModel', function () {
    $tipo = new TipoLicenciaReadModel();
    $tipo->exists = true;
    expect(fn () => $tipo->delete())
        ->toThrow(RuntimeException::class);
});
