<?php

declare(strict_types=1);

namespace App\Requests\Licencias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class NotificarLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fecha_notificacion' => ['nullable', 'date'],
            'fecha' => ['nullable', 'date'],
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
