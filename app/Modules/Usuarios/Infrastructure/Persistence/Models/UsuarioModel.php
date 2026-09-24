<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property bool $is_superuser
 * @property bool $is_staff
 * @property bool $is_active
 * @property string|null $date_joined
 * @property string|null $last_login
 * @property-read UsuarioPerfilModel|null $perfil
 */
final class UsuarioModel extends Model
{
    protected $table = 'auth_user';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'first_name',
        'last_name',
        'email',
        'is_superuser',
        'is_staff',
        'is_active',
        'date_joined',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasOne<UsuarioPerfilModel, $this>
     */
    public function perfil(): HasOne
    {
        return $this->hasOne(UsuarioPerfilModel::class, 'user_id');
    }
}
