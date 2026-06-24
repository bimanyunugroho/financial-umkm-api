<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $businessTypes = [
            'Warung Makan', 'Toko Kelontong', 'Konveksi', 'Toko Online',
            'Jasa Laundry', 'Bengkel Motor', 'Toko Baju', 'Bakery & Kue',
            'Toko Elektronik', 'Salon & Barbershop', 'Percetakan', 'Catering',
        ];

        return [
            'name'          => fake('id_ID')->name(),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => Hash::make('password'),
            'business_name' => fake('id_ID')->company(),
            'business_type' => fake()->randomElement($businessTypes),
            'phone'         => '08' . fake()->numerify('#########'),
            'address'       => fake('id_ID')->address(),
        ];
    }

    public function withEmail(string $email): static
    {
        return $this->state(['email' => $email, 'password' => Hash::make('password')]);
    }
}
