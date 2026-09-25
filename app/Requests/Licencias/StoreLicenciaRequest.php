<?php

declare(strict_types=1);

namespace App\Requests\Licencias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_licencia' => ['required'],
            'nombre_comercial' => ['required', 'string', 'max:250'],
            'tipo_licencia_id' => ['required', 'integer'],
            'titular_id' => ['nullable', 'integer'],
            'conductor_id' => ['nullable', 'integer'],
            'expediente_id' => ['nullable', 'integer'],
            'nivel_riesgo_id' => ['nullable', 'integer'],
            'zonificacion_id' => ['nullable', 'integer'],
            'licencia_principal_id' => ['nullable', 'integer'],
            'fecha_emision' => ['nullable', 'date'],
            'es_vigencia_indeterminada' => ['nullable', 'boolean'],
            'fecha_inicio_vigencia' => ['nullable', 'date'],
            'fecha_fin_vigencia' => ['nullable', 'date'],
            'actividad' => ['nullable', 'string', 'max:250'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'hora_desde' => ['nullable', 'integer'],
            'hora_hasta' => ['nullable', 'integer'],
            'resolucion_numero' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable'],
            'numero_recibo_pago' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
            'se_puede_publicar' => ['nullable', 'boolean'],
            'giros' => ['nullable', 'array'],
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
