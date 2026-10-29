<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReasignarInspectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'inspector_id' => ['required', 'integer', 'min:1'],
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
            'fecha_programada' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'indicaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return MensajesAsignacion::MENSAJES + [
            'motivo.required' => 'Indique el motivo de la reasignación.',
            'motivo.min' => 'Explique el motivo de la reasignación (al menos 10 caracteres).',
        ];
    }
}
