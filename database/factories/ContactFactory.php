<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
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
            'nombre' => fake()->name(),
            'numero' => fake()->phoneNumber(),
            'usuario' => fake()->unique()->userName(),
            'contrasena' => static::$password ??= Hash::make('password'),
            'correo' => fake()->unique()->safeEmail(),
        ];
    }
}
