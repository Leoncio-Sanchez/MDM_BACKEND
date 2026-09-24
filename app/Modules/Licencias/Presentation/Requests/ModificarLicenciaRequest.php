<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ModificarLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_comercial' => ['required', 'string', 'max:200'],
            'tipo_licencia_id' => ['required', 'integer', 'exists:tipos_licencia,id'],
            'titular_id' => ['nullable', 'integer', 'exists:personas,id'],
            'expediente_id' => ['nullable', 'integer', 'exists:expedientes,id'],
            'nivel_riesgo_id' => ['nullable', 'integer', 'exists:niveles_riesgo,id'],
            'zonificacion_id' => ['nullable', 'integer', 'exists:zonificaciones,id'],
        ];
    }
}
