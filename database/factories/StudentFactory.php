<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'student_id' => fake()->unique()->numerify('####-####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'grade_level' => 'Grade '.fake()->numberBetween(1, 12),
            'section' => 'Section '.fake()->randomLetter(),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'is_active' => true,
            'wallet_type' => fake()->randomElement(['subscribe', 'non-subscribe', null]),
            'qr_token' => Str::random(32),
        ];
    }

    /**
     * Mark the student as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set wallet type to subscribe.
     */
    public function withSubscribeWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_type' => 'subscribe',
        ]);
    }

    /**
     * Set wallet type to non-subscribe.
     */
    public function withNonSubscribeWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_type' => 'non-subscribe',
        ]);
    }

    /**
     * No wallet assigned.
     */
    public function withoutWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'wallet_type' => null,
        ]);
    }
}
