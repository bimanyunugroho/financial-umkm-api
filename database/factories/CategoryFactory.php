<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => null,
            'name'       => fake()->word(),
            'type'       => fake()->randomElement(TransactionType::cases()),
            'icon'       => 'tag',
            'color'      => fake()->hexColor(),
            'is_default' => false,
        ];
    }
}