<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CrearExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'numero_expediente' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'fecha_recepcion' => ['required', 'date'],
            'tipo_procedimiento_tupa_id' => ['required', 'integer', 'exists:tipos_procedimiento_tupa,id'],
            'solicitante_id' => ['required', 'integer', 'exists:personas,id'],
            'representante_id' => ['sometimes', 'nullable', 'integer', 'exists:personas,id'],
            'observaciones' => ['sometimes', 'nullable', 'string', 'max:250'],
        ];
    }
}
