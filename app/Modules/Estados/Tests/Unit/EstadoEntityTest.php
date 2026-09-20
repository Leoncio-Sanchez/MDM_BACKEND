<?php

declare(strict_types=1);

use App\Modules\Estados\Domain\Entities\Estado;
use App\Modules\Estados\Domain\Exceptions\EstadoNombreInvalido;

it('crea un estado valido', function () {
    $estado = Estado::crear('Registrado', true, true);
    expect($estado->nombre)->toBe('Registrado')
        ->and($estado->estaActivo)->toBeFalse();
});

it('lanza excepcion con nombre vacio', function () {
    Estado::crear('   ', true, true);
})->throws(EstadoNombreInvalido::class);

it('lanza excepcion con nombre mayor a 100 caracteres', function () {
    Estado::crear(str_repeat('a', 101), true, true);
})->throws(EstadoNombreInvalido::class);

it('actualiza un estado correctamente', function () {
    $estado = Estado::crear('Registrado', true, true);
    // simular DB
    $estadoDb = new Estado(1, $estado->nombre, $estado->esParaLf, $estado->esParaItse, $estado->estaActivo);

    $actualizado = $estadoDb->actualizar('Aprobado', false, null, true);

    expect($actualizado->nombre)->toBe('Aprobado')
        ->and($actualizado->esParaLf)->toBeFalse()
        ->and($actualizado->esParaItse)->toBeTrue()
        ->and($actualizado->estaActivo)->toBeTrue();
});
