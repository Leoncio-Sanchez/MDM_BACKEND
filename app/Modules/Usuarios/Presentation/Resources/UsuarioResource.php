<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Presentation\Resources;

use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read UsuarioModel $resource
 */
final class UsuarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $u = $this->resource;

        $firstName = (string) ($u->first_name ?? '');
        $lastName = (string) ($u->last_name ?? '');
        $fullName = trim($firstName.' '.$lastName);

        $perfil = null;
        if ($u->perfil !== null) {
            $perfil = [
                'expedientes' => (bool) $u->perfil->expedientes,
                'licencias' => (bool) $u->perfil->licencias,
                'itse' => (bool) $u->perfil->itse,
                'admin' => (bool) $u->perfil->admin,
            ];
        }

        return [
            'id' => (int) $u->id,
            'username' => (string) $u->username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'nombre_completo' => $fullName,
            'email' => (string) ($u->email ?? ''),
            'is_active' => (bool) $u->is_active,
            'is_staff' => (bool) $u->is_staff,
            'is_superuser' => (bool) $u->is_superuser,
            'date_joined' => $u->date_joined ? Carbon::parse($u->date_joined)->toISOString() : null,
            'last_login' => $u->last_login ? Carbon::parse($u->last_login)->toISOString() : null,
            'perfil' => $perfil,
        ];
    }
}
