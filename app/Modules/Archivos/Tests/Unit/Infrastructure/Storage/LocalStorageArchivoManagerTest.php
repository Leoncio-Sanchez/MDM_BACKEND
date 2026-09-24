<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Infrastructure\Storage;

use App\Modules\Archivos\Infrastructure\Storage\LocalStorageArchivoManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('media');
    $this->manager = new LocalStorageArchivoManager('media');
});

it('guarda archivo correctamente en disco media', function () {
    $file = UploadedFile::fake()->create('documento.pdf', 1024, 'application/pdf');

    $resultado = $this->manager->guardar($file, 'expedientes/15');

    expect($resultado)->toHaveKeys([
        'uuid',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'extension',
        'tamanio_bytes',
    ])
        ->and($resultado['nombre_original'])->toBe('documento.pdf')
        ->and($resultado['extension'])->toBe('pdf')
        ->and($resultado['ruta_archivo'])->toBe("expedientes/15/{$resultado['nombre_archivo']}")
        ->and($resultado['tamanio_bytes'])->toBe(1024 * 1024);

    Storage::disk('media')->assertExists($resultado['ruta_archivo']);
});

it('genera extension en minusculas y nombre unico con uuid', function () {
    $file = UploadedFile::fake()->create('PLANO_FINAL.DWG', 500);

    $resultado = $this->manager->guardar($file, 'itse/3');

    expect($resultado['extension'])->toBe('dwg')
        ->and($resultado['nombre_archivo'])->toMatch('/^[0-9a-f\-]{36}\.dwg$/')
        ->and($resultado['ruta_archivo'])->toBe("itse/3/{$resultado['nombre_archivo']}");
});

it('maneja archivos sin extension', function () {
    $file = UploadedFile::fake()->create('README', 200);

    $resultado = $this->manager->guardar($file, 'licencias_funcionamiento/7');

    expect($resultado['extension'])->toBe('')
        ->and($resultado['nombre_archivo'])->toMatch('/^[0-9a-f\-]{36}$/')
        ->and($resultado['ruta_archivo'])->toBe("licencias_funcionamiento/7/{$resultado['nombre_archivo']}");
});

it('elimina archivo existente en disco', function () {
    $file = UploadedFile::fake()->create('borrame.txt', 100);
    $resultado = $this->manager->guardar($file, 'expedientes/1');

    Storage::disk('media')->assertExists($resultado['ruta_archivo']);

    $this->manager->eliminar($resultado['ruta_archivo']);

    Storage::disk('media')->assertMissing($resultado['ruta_archivo']);
});

it('eliminar no falla si archivo no existe', function () {
    expect(fn () => $this->manager->eliminar('no/existe/archivo.pdf'))
        ->not->toThrow(\Throwable::class);
});

it('verifica existencia de archivo correctamente', function () {
    expect($this->manager->existe('no_existe.txt'))->toBeFalse();

    Storage::disk('media')->put('existente.txt', 'contenido de prueba');

    expect($this->manager->existe('existente.txt'))->toBeTrue();
});

it('lee stream de archivo correctamente', function () {
    Storage::disk('media')->put('prueba_stream.txt', 'Hola Mundo Stream');

    $stream = $this->manager->leer('prueba_stream.txt');

    if (is_resource($stream)) {
        $contenido = stream_get_contents($stream);
        fclose($stream);
    } else {
        $contenido = $stream;
    }

    expect($contenido)->toBe('Hola Mundo Stream');
});
