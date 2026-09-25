<?php

declare(strict_types=1);

namespace App\Requests\Expedientes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DenegarLicenciaRequest extends FormRequest
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
            'fecha_rechazo' => ['required', 'date'],
            'documento' => ['required', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
