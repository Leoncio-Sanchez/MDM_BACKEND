<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PorRenovarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return [
            // Rango sobre la fecha de caducidad. Por defecto: desde hoy hasta hoy + "dias".
            'fecha_desde' => ['sometimes', 'nullable', 'date'],
            'fecha_hasta' => ['sometimes', 'nullable', 'date', 'after_or_equal:fecha_desde'],
            'dias' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fecha_hasta.after_or_equal' => 'La fecha hasta no puede ser anterior a la fecha desde.',
            'dias.min' => 'Los días deben ser entre 1 y 365.',
            'dias.max' => 'Los días deben ser entre 1 y 365.',
        ];
    }
}
