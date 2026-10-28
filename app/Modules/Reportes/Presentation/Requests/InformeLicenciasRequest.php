<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InformeLicenciasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        // A diferencia del padrón, el informe formal lleva número: el periodo es obligatorio
        return [
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'desde.required' => 'Indique la fecha de inicio del mes.',
            'hasta.required' => 'Indique la fecha de fin del mes.',
            'hasta.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'desde.date_format' => 'La fecha de inicio no es válida.',
            'hasta.date_format' => 'La fecha de fin no es válida.',
        ];
    }
}
