<?php
declare(strict_types=1);

use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;

beforeEach(function () {
    $this->feriados = Mockery::mock(FeriadoReadRepositoryInterface::class);
    $this->calc = new CalculadoraDiasHabilesService($this->feriados);
});

afterEach(function () {
    Mockery::close();
});

it('avanza 5 días hábiles saltando fin de semana', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    // Viernes 2025-01-03 + 5 hábiles → Viernes 2025-01-10
    $inicio = new DateTimeImmutable('2025-01-03');
    $resultado = $this->calc->calcularFechaVencimiento($inicio, 5);

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-10')
        ->and($resultado->format('N'))->toBe('5');
});

it('salta feriado recurrente (05-01)', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => $f->format('m-d') === '05-01'
    );

    // 2025-05-01 = jueves. Desde 2025-04-28 (lunes) + 4 hábiles:
    // 29(1), 30(2), 05-01 SKIP, 05-02(3), 05-05(4) → 2025-05-05
    $inicio = new DateTimeImmutable('2025-04-28');
    $resultado = $this->calc->calcularFechaVencimiento($inicio, 4);

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-05-05')
        ->and($resultado->format('Y-m-d'))->not->toBe('2025-05-02');
});

it('retrocede días hábiles para la alerta', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    // Lunes 2025-01-13 - 3 hábiles → Miércoles 2025-01-08
    $vencimiento = new DateTimeImmutable('2025-01-13');
    $resultado = $this->calc->calcularFechaAlerta($vencimiento, 3);

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-08');
});

it('retorna la misma fecha si plazo es 0 o negativo', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $fecha = new DateTimeImmutable('2025-01-03');
    $resCero = $this->calc->calcularFechaVencimiento($fecha, 0);
    $resNegativo = $this->calc->calcularFechaVencimiento($fecha, -3);

    expect($resCero->format('Y-m-d'))->toBe('2025-01-03')
        ->and($resNegativo->format('Y-m-d'))->toBe('2025-01-03')
        ->and($resCero)->toBe($fecha);
});

it('retorna la misma fecha si diasAlerta es 0 o negativo', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $fecha = new DateTimeImmutable('2025-01-13');
    $resCero = $this->calc->calcularFechaAlerta($fecha, 0);
    $resNegativo = $this->calc->calcularFechaAlerta($fecha, -5);

    expect($resCero->format('Y-m-d'))->toBe('2025-01-13')
        ->and($resNegativo->format('Y-m-d'))->toBe('2025-01-13')
        ->and($resCero)->toBe($fecha);
});

it('NO muta el DateTimeImmutable original', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    $original = new DateTimeImmutable('2025-01-03');
    $resultado = $this->calc->calcularFechaVencimiento($original, 5);

    expect($original->format('Y-m-d'))->toBe('2025-01-03')
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-10')
        ->and($resultado)->not->toBe($original);
});

it('salta feriado anual específico', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => in_array($f->format('Y-m-d'), ['2026-04-02', '2026-04-03'], true)
    );

    // Jueves Santo 2026-04-02 y Viernes Santo 2026-04-03
    // Desde 2026-03-30 (Lunes) + 3 días hábiles:
    // 31(1), 04-01(2), 04-02 SKIP, 04-03 SKIP, Sáb 04-04 SKIP, Dom 04-05 SKIP, 04-06(3)
    $resultado = $this->calc->calcularFechaVencimiento(
        new DateTimeImmutable('2026-03-30'),
        3,
    );

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2026-04-06')
        ->and($resultado->format('N'))->toBe('1');
});

it('maneja plazo cayendo en fin de semana', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    // Miércoles 2025-01-08 + 2 hábiles → Viernes 2025-01-10
    $resultado = $this->calc->calcularFechaVencimiento(
        new DateTimeImmutable('2025-01-08'),
        2,
    );

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-10');
});

it('cruza fin de semana cuando plazo supera los días restantes de la semana', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);

    // Jueves 2025-01-09 + 2 hábiles: Vie 10 (1), Sáb 11 skip, Dom 12 skip, Lun 13 (2)
    $resultado = $this->calc->calcularFechaVencimiento(
        new DateTimeImmutable('2025-01-09'),
        2,
    );

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-13');
});

it('retrocede en días hábiles para la alerta saltando feriados y fin de semana', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => $f->format('Y-m-d') === '2025-01-09'
    );

    // Lunes 2025-01-13 - 3 días hábiles:
    // Dom 12 skip, Sáb 11 skip, Vie 10 (1), Jue 09 skip (feriado), Mié 08 (2), Mar 07 (3)
    $resultado = $this->calc->calcularFechaAlerta(
        new DateTimeImmutable('2025-01-13'),
        3,
    );

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-01-07');
});

it('esDiaHabil retorna false para fines de semana y feriados, y true para días hábiles', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => $f->format('Y-m-d') === '2025-01-08'
    );

    // Sábado
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-04')))->toBeFalse();
    // Domingo
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-05')))->toBeFalse();
    // Feriado (Miércoles)
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-08')))->toBeFalse();
    // Día hábil regular (Lunes)
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-06')))->toBeTrue();
    // Día hábil regular (Martes)
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-07')))->toBeTrue();
    // Día hábil regular (Viernes)
    expect($this->calc->esDiaHabil(new DateTimeImmutable('2025-01-10')))->toBeTrue();
});

it('maneja feriados consecutivos correctamente', function () {
    // 28 y 29 de Julio son feriados consecutivos en Perú (Fiestas Patrias)
    // 2025-07-28 (Lunes) y 2025-07-29 (Martes)
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => in_array($f->format('m-d'), ['07-28', '07-29'], true)
    );

    // Del Viernes 2025-07-25 + 3 días hábiles:
    // Sáb 26 skip, Dom 27 skip, Lun 28 skip, Mar 29 skip, Mié 30 (1), Jue 31 (2), Vie 08-01 (3)
    $resultado = $this->calc->calcularFechaVencimiento(
        new DateTimeImmutable('2025-07-25'),
        3,
    );

    expect($resultado)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($resultado->format('Y-m-d'))->toBe('2025-08-01')
        ->and($resultado->format('N'))->toBe('5');
});

it('retorna 0 si las fechas son iguales', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $fecha = new DateTimeImmutable('2025-01-03');
    expect($this->calc->diferenciaDiasHabiles($fecha, $fecha))->toBe(0);
});

it('cuenta días hábiles hacia adelante (exclusive inicio, inclusive fin)', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    // Viernes 2025-01-03 → Viernes 2025-01-10
    // Lun(6), Mar(7), Mié(8), Jue(9), Vie(10) = 5 días hábiles
    $desde = new DateTimeImmutable('2025-01-03');
    $hasta = new DateTimeImmutable('2025-01-10');
    expect($this->calc->diferenciaDiasHabiles($desde, $hasta))->toBe(5);
});

it('retorna negativo cuando fecha_hasta < fecha_desde', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $desde = new DateTimeImmutable('2025-01-10');
    $hasta = new DateTimeImmutable('2025-01-03');
    expect($this->calc->diferenciaDiasHabiles($desde, $hasta))->toBe(-5);
});

it('excluye feriados recurrentes', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturnUsing(
        fn (DateTimeImmutable $f) => $f->format('m-d') === '05-01'
    );
    // Con 05-01 feriado
    $desde = new DateTimeImmutable('2025-04-28');  // Lunes
    $hasta = new DateTimeImmutable('2025-05-06');  // Martes
    // Días hábiles: 29, 30, [01 skip], 02, 05, 06 = 5
    expect($this->calc->diferenciaDiasHabiles($desde, $hasta))->toBe(5);
});

it('maneja el día final si es inhábil', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    // Viernes 2025-01-03 → Domingo 2025-01-05
    // Sáb(4 skip), Dom(5 skip) = 0 días
    $desde = new DateTimeImmutable('2025-01-03');
    $hasta = new DateTimeImmutable('2025-01-05');
    expect($this->calc->diferenciaDiasHabiles($desde, $hasta))->toBe(0);
});

it('maneja un rango con un solo día hábil', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    // Jueves 2025-01-02 → Viernes 2025-01-03
    $desde = new DateTimeImmutable('2025-01-02');
    $hasta = new DateTimeImmutable('2025-01-03');
    expect($this->calc->diferenciaDiasHabiles($desde, $hasta))->toBe(1);
});

it('NO muta los DateTimeImmutable originales', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $desde = new DateTimeImmutable('2025-01-03 14:30:00');
    $hasta = new DateTimeImmutable('2025-01-10 18:00:00');
    $this->calc->diferenciaDiasHabiles($desde, $hasta);
    expect($desde->format('Y-m-d H:i:s'))->toBe('2025-01-03 14:30:00')
        ->and($hasta->format('Y-m-d H:i:s'))->toBe('2025-01-10 18:00:00');
});

it('maneja rangos largos (>30 días)', function () {
    $this->feriados->shouldReceive('esFeriado')->andReturn(false);
    $desde = new DateTimeImmutable('2025-01-01');
    $hasta = new DateTimeImmutable('2025-03-01');
    // Contar días hábiles en 59 días calendario
    $resultado = $this->calc->diferenciaDiasHabiles($desde, $hasta);
    expect($resultado)->toBeGreaterThan(0)->toBeLessThan(60);
});
