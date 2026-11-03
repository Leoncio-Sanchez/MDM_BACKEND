<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Requests;

use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;
use App\Shared\Domain\Calendario;
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
            'fecha_programada' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.Calendario::hoy()->toDateString()],
            'indicaciones' => ['nullable', 'string', 'max:500'],
            'acompanantes' => ['nullable', 'array', 'max:'.DatosAsignacion::MAXIMO_ACOMPANANTES],
            'acompanantes.*' => ['integer', 'min:1', 'distinct'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return MensajesAsignacion::MENSAJES;
    }
}
