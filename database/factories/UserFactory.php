<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use App\Support\UserRoles;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRoles::MANAGER,
            'is_active' => true,
            'location_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->role !== UserRoles::SUPER_ADMIN && ! $user->location_id) {
                $locationId = Location::query()->where('is_active', true)->value('id');

                if ($locationId) {
                    $user->update(['location_id' => $locationId]);
                }
            }
        });
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role' => UserRoles::SUPER_ADMIN,
            'location_id' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
