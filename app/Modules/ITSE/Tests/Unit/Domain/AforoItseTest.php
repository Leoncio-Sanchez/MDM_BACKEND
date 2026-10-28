<?php

declare(strict_types=1);

use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseInvalido;
use Carbon\CarbonImmutable;

/** Crea un ITSE con el área y aforo dados (el resto de datos no importa para la regla). */
function itseConAforo(float $area, int $aforo): Itse
{
    return Itse::crear('uuid', 1, CarbonImmutable::parse('2026-01-01'), null, CarbonImmutable::parse('2028-01-01'),
        'NEGOCIO', 'DIR', 'RES', $area, $aforo, 'REC', null, false, 1, 1, null, 1, 1, 1, 1, CarbonImmutable::now());
}

it('acepta hasta 2 personas por m² y rechaza más con un mensaje claro', function () {
    expect(itseConAforo(50, 100)->capacidadAforo)->toBe(100);

    expect(fn () => itseConAforo(50, 101))
        ->toThrow(ItseInvalido::class, 'Aforo excedido: el aforo máximo permitido es de 100 personas para un área de 50 m².');
});

it('al editar solo valida si cambian el área o el aforo', function () {
    // Certificado antiguo que no cumple la regla (se reconstruye desde la BD sin validar)
    $antiguo = new Itse(1, 'uuid', 1, CarbonImmutable::parse('2024-01-01'), null, CarbonImmutable::parse('2026-12-01'),
        'NEGOCIO', 'DIR', 'RES', 10, 80, 'REC', null, false, null, CarbonImmutable::now(), 1, 1, null, 1, 1, 1, 1);

    $editar = fn (float $area, int $aforo, string $nombre = 'NUEVO NOMBRE') => $antiguo->actualizar(
        $antiguo->fechaExpedicion, null, $antiguo->fechaCaducidad, $nombre, 'DIR', 'RES', $area, $aforo, 'REC', null, false, 1, 1, 1, 1);

    // Cambiar solo el nombre: permitido
    expect($editar(10, 80)->nombreComercial)->toBe('NUEVO NOMBRE');

    // Cambiar el aforo a un valor que tampoco cumple: rechazado
    expect(fn () => $editar(10, 70))->toThrow(ItseInvalido::class);

    // Corregirlo a un valor válido: permitido
    expect($editar(10, 20)->capacidadAforo)->toBe(20);
});
