<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Requests;

use App\Modules\Personas\Domain\ValueObjects\Sexo;
use App\Modules\Personas\Domain\ValueObjects\TipoPersona;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrearPersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo_persona' => ['required', 'string', Rule::enum(TipoPersona::class)],
            'sexo' => ['required', 'string', Rule::enum(Sexo::class)],
            'apellido_paterno' => ['nullable', 'string', 'max:50'],
            'apellido_materno' => ['nullable', 'string', 'max:50'],
            'nombres' => ['required', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'distrito' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'documentos' => ['array'],
            'documentos.*.tipo_documento_identidad_id' => ['required', 'integer'],
            'documentos.*.numero_documento' => ['required', 'string', 'max:20'],
        ];
    }
}
