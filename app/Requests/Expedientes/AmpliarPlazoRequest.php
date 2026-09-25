<?php

declare(strict_types=1);

namespace App\Requests\Expedientes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AmpliarPlazoRequest extends FormRequest
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
            'fecha_suspension' => ['required', 'date'],
            'dias_ampliacion' => ['required', 'integer', 'min:1'],
            'motivo_ampliacion' => ['required', 'string', 'max:250'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
