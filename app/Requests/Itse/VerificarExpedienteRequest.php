<?php

declare(strict_types=1);

namespace App\Requests\Itse;

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
            'numero_expediente' => ['required', 'integer'],
            'anio' => ['required', 'integer'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        if ($errors->has('numero_expediente')) {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'numero_expediente' es obligatorio."], 400)
            );
        }

        if ($errors->has('anio')) {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'anio' es obligatorio."], 400)
            );
        }

        $firstError = $errors->first();
        throw new HttpResponseException(
            response()->json(['error' => $firstError], 400)
        );
    }
}
