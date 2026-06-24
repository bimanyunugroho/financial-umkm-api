<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    private static array $incomeDescriptions = [
        'Penjualan produk harian', 'Pembayaran pesanan online', 'Penjualan grosir',
        'Pendapatan jasa', 'Penjualan via marketplace', 'Pembayaran DP customer',
        'Pelunasan tagihan customer', 'Pendapatan konsultasi', 'Penjualan tunai',
        'Transfer dari customer', 'Pembayaran QRIS', 'Penjualan event weekend',
    ];

    private static array $expenseDescriptions = [
        'Pembelian bahan baku', 'Bayar listrik & air', 'Gaji karyawan',
        'Sewa tempat usaha', 'Pembelian perlengkapan', 'Biaya pengiriman',
        'Iklan & promosi', 'Pembelian packaging', 'Service peralatan',
        'Pembelian stok barang', 'Bayar internet & telepon', 'Biaya administrasi',
        'Pembelian alat tulis', 'Bayar BPJS karyawan', 'Biaya operasional lainnya',
    ];

    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        $amount = match ($type) {
            TransactionType::Income  => fake()->randomElement([
                fake()->numberBetween(50_000, 500_000),
                fake()->numberBetween(500_000, 2_000_000),
                fake()->numberBetween(2_000_000, 10_000_000),
            ]),
            TransactionType::Expense => fake()->randomElement([
                fake()->numberBetween(10_000, 200_000),
                fake()->numberBetween(200_000, 1_000_000),
                fake()->numberBetween(1_000_000, 5_000_000),
            ]),
        };

        $descriptions = $type === TransactionType::Income
            ? self::$incomeDescriptions
            : self::$expenseDescriptions;

        return [
            'user_id'          => User::factory(),
            'category_id'      => Category::factory(),
            'type'             => $type,
            'amount'           => $amount,
            'description'      => fake()->randomElement($descriptions),
            'date'             => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'payment_method'   => fake()->randomElement(PaymentMethod::cases()),
            'notes'            => fake()->optional(0.3)->sentence(),
            'reference_number' => fake()->optional(0.2)->numerify('INV-####-####'),
        ];
    }

    public function income(): static
    {
        return $this->state([
            'type'        => TransactionType::Income,
            'amount'      => fake()->numberBetween(100_000, 5_000_000),
            'description' => fake()->randomElement(self::$incomeDescriptions),
        ]);
    }

    public function expense(): static
    {
        return $this->state([
            'type'        => TransactionType::Expense,
            'amount'      => fake()->numberBetween(10_000, 2_000_000),
            'description' => fake()->randomElement(self::$expenseDescriptions),
        ]);
    }

    public function thisMonth(): static
    {
        return $this->state([
            'date' => fake()->dateTimeBetween('first day of this month', 'now')->format('Y-m-d'),
        ]);
    }

    public function inMonth(int $year, int $month): static
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        return $this->state([
            'date' => fake()->dateTimeBetween($start, $end)->format('Y-m-d'),
        ]);
    }
}
