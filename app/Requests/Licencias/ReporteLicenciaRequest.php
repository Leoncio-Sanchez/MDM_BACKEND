<?php

declare(strict_types=1);

namespace App\Requests\Licencias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReporteLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_licencia' => ['nullable'],
            'emision_desde' => ['nullable', 'date'],
            'emision_hasta' => ['nullable', 'date'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
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
