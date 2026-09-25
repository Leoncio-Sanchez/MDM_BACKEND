<?php

declare(strict_types=1);

namespace App\Requests\Itse;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ActualizarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_itse' => ['sometimes', 'integer'],
            'fecha_expedicion' => ['sometimes', 'date'],
            'fecha_solicitud_renovacion' => ['sometimes', 'date'],
            'fecha_caducidad' => ['sometimes', 'date'],
            'nombre_comercial' => ['sometimes', 'string', 'max:250'],
            'direccion' => ['sometimes', 'string', 'max:250'],
            'resolucion_numero' => ['sometimes', 'string', 'max:50'],
            'area' => ['sometimes'],
            'capacidad_aforo' => ['sometimes', 'integer', 'min:0'],
            'numero_recibo_pago' => ['sometimes', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'se_puede_publicar' => ['sometimes', 'boolean'],
            'tipo_itse_id' => ['sometimes', 'integer', 'in:1,2'],
            'expediente_id' => ['sometimes', 'integer'],
            'itse_principal_id' => ['nullable', 'integer'],
            'nivel_riesgo_id' => ['sometimes', 'integer'],
            'conductor_id' => ['sometimes', 'integer'],
            'titular_id' => ['sometimes', 'integer'],
            'giros' => ['sometimes', 'array'],
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
