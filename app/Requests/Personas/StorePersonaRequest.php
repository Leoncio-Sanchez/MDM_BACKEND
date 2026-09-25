<?php

declare(strict_types=1);

namespace App\Requests\Personas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePersonaRequest extends FormRequest
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
            'tipo_persona' => ['required', 'string', 'in:N,J'],
            'sexo' => ['nullable', 'string', 'in:M,F,X'],
            'apellido_paterno' => ['nullable', 'string', 'max:50'],
            'apellido_materno' => ['nullable', 'string', 'max:50'],
            'nombres' => ['required', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:250'],
            'distrito' => ['required', 'string', 'max:100'],
            'provincia' => ['required', 'string', 'max:100'],
            'departamento' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'documentos' => ['required', 'array'],
            'documentos.*.tipo_documento_identidad_id' => ['required', 'integer'],
            'documentos.*.numero_documento' => ['required', 'string', 'max:20'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
