<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Usuario
 *
 * @property int $id
 * @property string $username
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property bool $is_active
 * @property bool $is_staff
 * @property bool $is_superuser
 * @property mixed $date_joined
 * @property mixed $last_login
 * @property mixed $perfil
 */
class UsuarioResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $firstName = (string) ($this->first_name ?? '');
        $lastName = (string) ($this->last_name ?? '');
        $fullName = trim($firstName.' '.$lastName);
        if ($fullName === '') {
            $fullName = (string) $this->username;
        }

        $dateJoinedStr = null;
        if ($this->date_joined !== null) {
            $dateJoinedStr = Carbon::parse($this->date_joined)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
        }

        $lastLoginStr = null;
        if ($this->last_login !== null) {
            $lastLoginStr = Carbon::parse($this->last_login)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP');
        }

        $perfil = null;
        if ($this->perfil !== null) {
            $perfil = [
                'expedientes' => (bool) $this->perfil->expedientes,
                'licencias' => (bool) $this->perfil->licencias,
                'itse' => (bool) $this->perfil->itse,
                'admin' => (bool) $this->perfil->admin,
            ];
        }

        return [
            'id' => (int) $this->id,
            'username' => (string) $this->username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'nombre_completo' => $fullName,
            'email' => (string) ($this->email ?? ''),
            'is_active' => (bool) $this->is_active,
            'is_staff' => (bool) $this->is_staff,
            'is_superuser' => (bool) $this->is_superuser,
            'date_joined' => $dateJoinedStr,
            'last_login' => $lastLoginStr,
            'perfil' => $perfil,
        ];
    }
}
