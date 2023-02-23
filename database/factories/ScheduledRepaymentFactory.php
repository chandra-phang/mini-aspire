<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduledRepayment>
 */
class ScheduledRepaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => null,
            'payable_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'PENDING',
            'due_date' => fake()->dateTime(),
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PAID',
            'paid_amount' => 1000,
            'paid_at' => fake()->dateTime(),
        ]);
    }
}
