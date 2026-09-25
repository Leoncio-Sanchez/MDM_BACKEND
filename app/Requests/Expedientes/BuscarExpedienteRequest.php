<?php

declare(strict_types=1);

namespace App\Requests\Expedientes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BuscarExpedienteRequest extends FormRequest
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
            'filtro' => ['required', 'string'],
            'valor' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filtro.required' => "El parámetro 'filtro' es obligatorio.",
            'valor.required' => "El parámetro 'valor' es obligatorio.",
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(
            response()->json(['error' => $firstError], 400)
        );
    }
}
