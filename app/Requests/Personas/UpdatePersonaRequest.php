<?php

declare(strict_types=1);

namespace App\Requests\Personas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePersonaRequest extends FormRequest
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
            'tipo_persona' => ['sometimes', 'required', 'string', 'in:N,J'],
            'sexo' => ['nullable', 'string', 'in:M,F,X'],
            'apellido_paterno' => ['nullable', 'string', 'max:50'],
            'apellido_materno' => ['nullable', 'string', 'max:50'],
            'nombres' => ['sometimes', 'required', 'string', 'max:100'],
            'direccion' => ['sometimes', 'required', 'string', 'max:250'],
            'distrito' => ['sometimes', 'required', 'string', 'max:100'],
            'provincia' => ['sometimes', 'required', 'string', 'max:100'],
            'departamento' => ['sometimes', 'required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'documentos' => ['sometimes', 'required', 'array'],
            'documentos.*.tipo_documento_identidad_id' => ['required_with:documentos', 'integer'],
            'documentos.*.numero_documento' => ['required_with:documentos', 'string', 'max:20'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
