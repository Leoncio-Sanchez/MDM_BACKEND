<?php

declare(strict_types=1);

namespace App\Requests\Usuarios;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:150'],
            'last_name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:254'],
            'is_active' => ['nullable', 'boolean'],
            'is_staff' => ['nullable', 'boolean'],
            'perfil' => ['nullable', 'array'],
            'perfil.expedientes' => ['nullable', 'boolean'],
            'perfil.licencias' => ['nullable', 'boolean'],
            'perfil.itse' => ['nullable', 'boolean'],
            'perfil.admin' => ['nullable', 'boolean'],
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
