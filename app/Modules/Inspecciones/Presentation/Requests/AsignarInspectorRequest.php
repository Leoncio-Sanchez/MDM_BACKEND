<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AsignarInspectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'expediente_id' => ['required', 'integer', 'min:1'],
            'inspector_id' => ['required', 'integer', 'min:1'],
            'nombre_comercial' => ['required', 'string', 'max:250'],
            'direccion' => ['required', 'string', 'max:250'],
            'fecha_programada' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'indicaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return MensajesAsignacion::MENSAJES;
    }
}
