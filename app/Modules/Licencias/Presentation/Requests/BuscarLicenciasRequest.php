<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class BuscarLicenciasRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'filtro' => ['required', 'string', 'in:NUMERO_LICENCIA,NOMBRE_COMERCIAL,EXPEDIENTE'],
            'valor' => ['required', 'string'],
    ];
    }
}
