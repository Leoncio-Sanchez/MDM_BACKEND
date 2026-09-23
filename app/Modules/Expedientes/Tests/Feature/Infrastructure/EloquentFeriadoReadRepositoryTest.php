<?php
declare(strict_types=1);

use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentFeriadoReadRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentFeriadoReadRepository();
});

it('detecta feriado recurrente (28-07)', function () {
    DB::table('feriados_recurrentes')->insert([
        ['id' => 1, 'dia' => 28, 'mes' => 7],
    ]);

    expect($this->repo->esFeriado(new DateTimeImmutable('2025-07-28')))->toBeTrue();
});

it('detecta feriado anual', function () {
    DB::table('feriados_anuales')->insert([
        ['id' => 1, 'feriado' => '2026-04-02', 'fecha_digitacion' => now(), 'user_id' => 1],
    ]);

    expect($this->repo->esFeriado(new DateTimeImmutable('2026-04-02')))->toBeTrue();
});

it('retorna false para día hábil normal', function () {
    expect($this->repo->esFeriado(new DateTimeImmutable('2025-06-15')))->toBeFalse();
});
