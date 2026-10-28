<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            // Sin fechas: el mes en curso
            'desde' => ['nullable', 'date_format:Y-m-d', 'required_with:hasta'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'required_with:desde', 'after_or_equal:desde'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'desde.required_with' => 'Indique también la fecha de inicio.',
            'hasta.required_with' => 'Indique también la fecha de fin.',
            'hasta.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'desde.date_format' => 'La fecha de inicio no es válida.',
            'hasta.date_format' => 'La fecha de fin no es válida.',
        ];
    }
}
