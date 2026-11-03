<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => app()->setLocale('es'));

it('traduce todas las reglas de validación del framework', function (string $archivo) {
    $ingles = require base_path("vendor/laravel/framework/src/Illuminate/Translation/lang/en/{$archivo}.php");
    $espanol = require lang_path("es/{$archivo}.php");

    expect(array_diff(array_keys(Arr::dot($ingles)), array_keys(Arr::dot($espanol)), ['custom.attribute-name.rule-name', 'attributes']))->toBe([]);
})->with(['validation', 'auth', 'passwords', 'pagination']);

it('responde en español los errores de validación del login', function () {
    $this->postJson('/api/auth/jwt/create/', [])->assertStatus(400)->assertExactJson([
        'username' => ['El campo usuario es obligatorio.'],
        'password' => ['El campo contraseña es obligatorio.'],
    ]);
});

it('resume en español cuando hay varios errores', function () {
    $errores = Validator::make([], ['documento' => 'required', 'numero' => 'required', 'direccion' => 'required']);

    expect((new ValidationException($errores))->getMessage())
        ->toBe('El campo documento es obligatorio. (y 2 errores más)');
});

it('responde en español las reglas de contraseña', function () {
    $errores = Validator::make(['clave' => '!!!'], ['clave' => Password::min(12)->letters()->numbers()])->errors()->get('clave');

    expect($errores)->toBe([
        'El campo contraseña debe tener al menos 12 caracteres.',
        'El campo contraseña debe contener al menos una letra.',
        'El campo contraseña debe contener al menos un número.',
    ]);
});

it('usa español en el ejemplo de producción', function () {
    expect((string) file_get_contents(base_path('.env.production.example')))->toContain("\nAPP_LOCALE=es\n");
});
