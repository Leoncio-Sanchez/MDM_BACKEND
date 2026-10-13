<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Usuarios de `auth_user` (esquema de Django).
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            // Hash con formato Django válido (pbkdf2_sha256) aunque no se use para iniciar sesión
            'password' => 'pbkdf2_sha256$1200000$factorysalt$' . base64_encode(random_bytes(32)),
            'is_superuser' => false,
            'is_staff' => false,
            'is_active' => true,
            'date_joined' => now(),
        ];
    }

    /** Personal municipal con acceso a la gestión (rol ADMIN_MUNICIPALIDAD). */
    public function administrador(): static
    {
        return $this->state(fn () => ['is_staff' => true]);
    }
}
