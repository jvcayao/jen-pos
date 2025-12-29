<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Store;
use App\Models\Student;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
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
            'uuid' => (string) Str::uuid(),
            'type' => 'pos',
            'user_id' => User::factory(),
            'account_id' => User::factory(),
            'cashier_id' => User::factory(),
            'source' => 'pos',
            'total' => fake()->randomFloat(2, 50, 1000),
            'discount' => 0,
            'shipping' => 0,
            'vat' => fake()->randomFloat(2, 5, 100),
            'status' => 'confirm',
            'is_void' => false,
            'is_payed' => true,
            'payment_method' => 'cash',
        ];
    }

    /**
     * Mark the order as pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'is_payed' => false,
        ]);
    }

    /**
     * Mark the order as void.
     */
    public function void(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'void',
            'is_void' => true,
        ]);
    }

    /**
     * Associate with a student.
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
            'store_id' => $student->store_id,
        ]);
    }

    /**
     * Set payment method to wallet.
     */
    public function paidWithWallet(string $walletType = 'subscribe'): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'wallet',
            'wallet_type' => $walletType,
        ]);
    }
}
