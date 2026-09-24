<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Tests\Unit\Infrastructure;

use App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher;

it('valida correctamente un hash pbkdf2_sha256 real de Django', function () {
    $hasher = new DjangoPbkdf2Hasher;
    $realHash = 'pbkdf2_sha256$1200000$bCyHs02f8l03epJzsd0N0D$s+PXHa+tN/qJkDe7bnCgcCFghprjFNI2xLALtbqwW3U=';

    expect($hasher->verify('admin', $realHash))->toBeTrue();
});

it('retorna false con password incorrecta', function () {
    $hasher = new DjangoPbkdf2Hasher;
    $realHash = 'pbkdf2_sha256$1200000$bCyHs02f8l03epJzsd0N0D$s+PXHa+tN/qJkDe7bnCgcCFghprjFNI2xLALtbqwW3U=';

    expect($hasher->verify('wrong_password', $realHash))->toBeFalse();
});

it('retorna false si formato es inválido (no 4 partes)', function () {
    $hasher = new DjangoPbkdf2Hasher;

    expect($hasher->verify('admin', 'formato$invalido'))->toBeFalse()
        ->and($hasher->verify('admin', 'sin_delimitadores'))->toBeFalse()
        ->and($hasher->verify('admin', 'a$b$c$d$extra'))->toBeFalse();
});

it('retorna false si algoritmo no es pbkdf2_sha256', function () {
    $hasher = new DjangoPbkdf2Hasher;
    $fakeHash = 'argon2id$1200000$salt$hash';

    expect($hasher->verify('admin', $fakeHash))->toBeFalse();
});

it('retorna false si las iteraciones no son válidas', function () {
    $hasher = new DjangoPbkdf2Hasher;
    $invalidIterHash = 'pbkdf2_sha256$0$salt$hash';

    expect($hasher->verify('admin', $invalidIterHash))->toBeFalse();
});

it('valida correctamente con salt y contraseña arbitraria generada', function () {
    $hasher = new DjangoPbkdf2Hasher;
    $password = 'secretPassword123!';
    $salt = 'randomSalt456789';
    $iterations = 10000;

    $binary = hash_pbkdf2('sha256', $password, $salt, $iterations, 0, true);
    $b64 = base64_encode($binary);
    $djangoHash = "pbkdf2_sha256\${$iterations}\${$salt}\${$b64}";

    expect($hasher->verify($password, $djangoHash))->toBeTrue()
        ->and($hasher->verify('anotherPassword', $djangoHash))->toBeFalse();
});
