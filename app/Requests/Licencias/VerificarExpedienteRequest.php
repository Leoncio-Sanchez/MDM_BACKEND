<?php

declare(strict_types=1);

namespace App\Requests\Licencias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerificarExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_expediente' => ['required'],
            'anio' => ['required'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'numero_expediente.required' => "El parámetro 'numero_expediente' es obligatorio.",
            'anio.required' => "El parámetro 'anio' es obligatorio.",
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
