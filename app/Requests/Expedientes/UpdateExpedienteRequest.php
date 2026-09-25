<?php

declare(strict_types=1);

namespace App\Requests\Expedientes;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateExpedienteRequest extends FormRequest
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
            'numero_expediente' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'fecha_recepcion' => ['sometimes', 'required', 'date'],
            'tipo_procedimiento_tupa_id' => ['sometimes', 'required', 'integer'],
            'solicitante_id' => ['sometimes', 'required', 'integer'],
            'representante_id' => ['sometimes', 'nullable', 'integer'],
            'observaciones' => ['sometimes', 'nullable', 'string', 'max:250'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
