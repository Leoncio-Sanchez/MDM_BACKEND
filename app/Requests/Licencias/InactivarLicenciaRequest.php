<?php

declare(strict_types=1);

namespace App\Requests\Licencias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InactivarLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'licencia_id' => ['nullable', 'integer'],
            'licencia_funcionamiento_id' => ['nullable', 'integer'],
            'estado_id' => ['nullable', 'integer'],
            'fecha_estado' => ['nullable', 'date'],
            'documento' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
            'motivo' => ['nullable', 'string'],
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
