<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Requests;

use App\Modules\Archivos\Domain\PoliticaArchivos;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosActa;
use Illuminate\Foundation\Http\FormRequest;

/** Se envía como multipart: datos del acta y fotos en un solo pedido */
final class RegistrarActaRequest extends FormRequest
{
    public const MAXIMO_FOTOS = 10;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $estados = 'in:CUMPLE,NO_CUMPLE,NO_APLICA';
        $reglas = [
            'numero_acta' => ['required', 'string', 'max:30'],
            'fecha_visita' => ['required', 'date'],
            'area_m2' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'aforo' => ['required', 'integer', 'min:0', 'max:1000000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'plazo_subsanacion_dias' => ['nullable', 'integer', 'in:'.implode(',', DatosActa::PLAZOS_SUBSANACION)],
            'fotos' => ['required', 'array', 'min:1', 'max:'.self::MAXIMO_FOTOS],
            'fotos.*.tipo' => ['required', 'in:FACHADA,EXTINTORES,TABLEROS,OTRA'],
            'fotos.*.archivo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:'.PoliticaArchivos::TAMANIO_MAXIMO_KB],
        ];
        foreach (array_keys(DatosActa::GRUPOS) as $grupo) {
            $reglas[$grupo] = ['required', $estados];
        }

        return $reglas;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'numero_acta.required' => 'Indique el número de acta.',
            'fecha_visita.required' => 'Indique la fecha y hora de la visita.',
            'area_m2.required' => 'Indique el metraje del local.',
            'area_m2.gt' => 'El metraje debe ser mayor que cero.',
            'aforo.required' => 'Indique el aforo comprobado.',
            '*.required' => 'Complete el checklist.',
            '*.in' => 'Valor no permitido.',
            'fotos.required' => 'Adjunte las fotos de sustento.',
            'fotos.max' => 'Se permiten como máximo '.self::MAXIMO_FOTOS.' fotos.',
            'fotos.*.archivo.mimes' => 'Las fotos deben ser JPG o PNG.',
            'fotos.*.archivo.max' => 'Cada foto puede pesar como máximo 10 MB.',
        ];
    }
}
