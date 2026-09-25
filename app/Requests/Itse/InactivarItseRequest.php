<?php

declare(strict_types=1);

namespace App\Requests\Itse;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class InactivarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'itse_id' => ['required', 'integer'],
            'estado_id' => ['required', 'integer'],
            'fecha_estado' => ['required', 'date'],
            'documento' => ['required', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:1000'],
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
