<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ActualizarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api') !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'numero_itse' => ['required', 'integer'],
            'fecha_expedicion' => ['required', 'date'],
            'fecha_solicitud_renovacion' => ['nullable', 'date'],
            'fecha_caducidad' => ['required', 'date'],
            'nombre_comercial' => ['required', 'string', 'max:250'],
            'direccion' => ['required', 'string', 'max:250'],
            'resolucion_numero' => ['required', 'string', 'max:50'],
            'area' => ['required', 'numeric'],
            'capacidad_aforo' => ['sometimes', 'integer', 'min:0'],
            'numero_recibo_pago' => ['required', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'se_puede_publicar' => ['sometimes', 'boolean'],
            'tipo_itse_id' => ['required', 'integer', 'in:1,2'],
            'nivel_riesgo_id' => ['required', 'integer'],
            'conductor_id' => ['required', 'integer'],
            'titular_id' => ['required', 'integer'],
        ];
    }
}
