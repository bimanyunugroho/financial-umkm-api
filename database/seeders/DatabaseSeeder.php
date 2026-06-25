<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $demo = User::firstOrCreate(
            ['email' => 'demo@gmail.com'],
            [
                'name'          => 'Demo',
                'password'      => Hash::make('password123'),
                'business_name' => 'Warung Makan Demo',
                'business_type' => 'Warung Makan',
                'phone'         => '081234567890',
                'address'       => 'Jl. Sudirman No. 12, Jakarta Selatan',
            ]
        );

        $this->seedTransactions($demo);

        User::factory(3)->create()->each(function (User $user) {
            $this->seedTransactions($user);
        });

        $this->command->info('Database seeded successfully!');
        $this->command->line('Demo user: demo@gmail.com / password123');
    }

    private function seedTransactions(User $user): void
    {
        $categories = Category::whereNull('user_id')->get();

        $incomeCategories = $categories->where('type', 'income');
        $expenseCategories = $categories->where('type', 'expense');

        $createdTransactions = collect();

        for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {

            $date = Carbon::now()->subMonths($monthOffset);

            $year = $date->year;
            $month = $date->month;
            $daysInMonth = $date->daysInMonth;

            // Income
            $incomeCount = rand(15, 25);

            for ($i = 0; $i < $incomeCount; $i++) {

                $day = rand(
                    1,
                    min(
                        $daysInMonth,
                        $monthOffset === 0 ? now()->day : $daysInMonth
                    )
                );

                $transaction = Transaction::factory()
                    ->income()
                    ->for($user)
                    ->create([
                        'category_id' => $incomeCategories->random()->id,
                        'date'        => Carbon::create($year, $month, $day)->toDateString(),
                        'amount'      => $this->realisticIncomeAmount(),
                    ]);

                $createdTransactions->push($transaction);

                activity()
                    ->causedBy($user)
                    ->performedOn($transaction)
                    ->event('created')
                    ->withProperties([
                        'amount' => $transaction->amount,
                        'type'   => $transaction->type,
                    ])
                    ->log('Menambahkan transaksi pemasukan');
            }

            // Expense
            $expenseCount = rand(20, 35);

            for ($i = 0; $i < $expenseCount; $i++) {

                $day = rand(
                    1,
                    min(
                        $daysInMonth,
                        $monthOffset === 0 ? now()->day : $daysInMonth
                    )
                );

                $transaction = Transaction::factory()
                    ->expense()
                    ->for($user)
                    ->create([
                        'category_id' => $expenseCategories->random()->id,
                        'date'        => Carbon::create($year, $month, $day)->toDateString(),
                        'amount'      => $this->realisticExpenseAmount(),
                    ]);

                $createdTransactions->push($transaction);

                activity()
                    ->causedBy($user)
                    ->performedOn($transaction)
                    ->event('created')
                    ->withProperties([
                        'amount' => $transaction->amount,
                        'type'   => $transaction->type,
                    ])
                    ->log('Menambahkan transaksi pengeluaran');
            }
        }

        // Simulasi UPDATE
        $updatedTransactions = $createdTransactions
            ->shuffle()
            ->take(min(20, $createdTransactions->count()));

        foreach ($updatedTransactions as $transaction) {

            activity()
                ->causedBy($user)
                ->performedOn($transaction)
                ->event('updated')
                ->withProperties([
                    'amount' => $transaction->amount,
                    'type'   => $transaction->type,
                ])
                ->log('Mengubah transaksi');
        }

        // Simulasi DELETE
        $deletedTransactions = $createdTransactions
            ->shuffle()
            ->take(min(10, $createdTransactions->count()));

        foreach ($deletedTransactions as $transaction) {

            activity()
                ->causedBy($user)
                ->performedOn($transaction)
                ->event('deleted')
                ->withProperties([
                    'amount' => $transaction->amount,
                    'type'   => $transaction->type,
                ])
                ->log('Menghapus transaksi');
        }
    }

    private function realisticIncomeAmount(): int
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 50 => rand(50_000, 500_000),
            $rand <= 80 => rand(500_000, 2_000_000),
            default     => rand(2_000_000, 8_000_000),
        };
    }

    private function realisticExpenseAmount(): int
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 60 => rand(10_000, 300_000),
            $rand <= 85 => rand(300_000, 1_500_000),
            default     => rand(1_500_000, 5_000_000),
        };
    }
}