<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ActualizarConfiguracionInformeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'entidad' => ['required', 'string', 'max:200'],
            'nombre_anio' => ['nullable', 'string', 'max:250'],
            'gerencia' => ['required', 'string', 'max:200'],
            // Va en el número del informe: INFORME N° 012-2026-MDM/GDES
            'siglas' => ['required', 'string', 'max:40', 'regex:/^[A-ZÑ0-9][A-ZÑ0-9\/\-]*$/u'],
            'firmante_nombre' => ['nullable', 'string', 'max:150'],
            'firmante_cargo' => ['required', 'string', 'max:150'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'entidad.required' => 'Indique el nombre de la entidad.',
            'gerencia.required' => 'Indique la gerencia que emite el informe.',
            'siglas.required' => 'Indique las siglas del informe (ej. MDM/GDES).',
            'siglas.regex' => 'Las siglas solo pueden tener mayúsculas, números, "/" y "-" (ej. MDM/GDES).',
            'firmante_cargo.required' => 'Indique el cargo de quien firma.',
            '*.max' => 'El texto es demasiado largo.',
        ];
    }
}
