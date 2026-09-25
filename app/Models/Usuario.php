<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
 * @property Carbon|string|null $date_joined
 * @property Carbon|string|null $last_login
 * @property-read UsuarioPerfil|null $perfil
 * @property-read string $nombre_completo
 *
 * @method static Builder<static>|Usuario activos()
 * @method static Builder<static>|Usuario inactivos()
 * @method static Usuario create(array<string, mixed> $attributes = [])
 * @method static Usuario findOrFail(mixed $id, array<string> $columns = ['*'])
 * @method static Builder<static>|Usuario query()
 */
class Usuario extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
            'date_joined' => 'datetime',
            'last_login' => 'datetime',
        ];
    }

    /**
     * @return HasOne<UsuarioPerfil, $this>
     */
    public function perfil(): HasOne
    {
        return $this->hasOne(UsuarioPerfil::class, 'user_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        $fullName = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));

        return $fullName !== '' ? $fullName : (string) $this->username;
    }

    public function estaActivo(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * @param  Builder<Usuario>  $query
     * @return Builder<Usuario>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Usuario>  $query
     * @return Builder<Usuario>
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'id' => $this->id,
            'user_id' => (string) $this->id,
            'username' => $this->username,
            'role' => $this->is_superuser ? 'ADMIN_MUNICIPALIDAD' : 'USUARIO',
            'ruc' => null,
            'token_type' => 'access',
        ];
    }

    protected static function newFactory(): UsuarioFactory
    {
        return UsuarioFactory::new();
    }
}
