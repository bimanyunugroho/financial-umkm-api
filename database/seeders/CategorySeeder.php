<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ── Income Categories ──────────────────────────────────────────
            ['name' => 'Penjualan Produk',    'type' => 'income',  'icon' => 'shopping-bag',    'color' => '#10b981'],
            ['name' => 'Penjualan Jasa',      'type' => 'income',  'icon' => 'briefcase',       'color' => '#3b82f6'],
            ['name' => 'Penjualan Online',    'type' => 'income',  'icon' => 'device-laptop',   'color' => '#8b5cf6'],
            ['name' => 'Pendapatan Lainnya',  'type' => 'income',  'icon' => 'plus-circle',     'color' => '#6366f1'],
            ['name' => 'Bonus & Komisi',      'type' => 'income',  'icon' => 'star',            'color' => '#f59e0b'],
            ['name' => 'Pendapatan Sewa',     'type' => 'income',  'icon' => 'home',            'color' => '#14b8a6'],

            // ── Expense Categories ─────────────────────────────────────────
            ['name' => 'Bahan Baku',          'type' => 'expense', 'icon' => 'package',         'color' => '#ef4444'],
            ['name' => 'Gaji Karyawan',       'type' => 'expense', 'icon' => 'users',           'color' => '#f97316'],
            ['name' => 'Sewa Tempat',         'type' => 'expense', 'icon' => 'building',        'color' => '#ec4899'],
            ['name' => 'Listrik & Air',       'type' => 'expense', 'icon' => 'bolt',            'color' => '#eab308'],
            ['name' => 'Internet & Telepon',  'type' => 'expense', 'icon' => 'wifi',            'color' => '#06b6d4'],
            ['name' => 'Iklan & Promosi',     'type' => 'expense', 'icon' => 'megaphone',       'color' => '#a855f7'],
            ['name' => 'Peralatan & Mesin',   'type' => 'expense', 'icon' => 'tool',            'color' => '#64748b'],
            ['name' => 'Transportasi',        'type' => 'expense', 'icon' => 'truck',           'color' => '#84cc16'],
            ['name' => 'Biaya Pengiriman',    'type' => 'expense', 'icon' => 'send',            'color' => '#0ea5e9'],
            ['name' => 'Biaya Admin & Bank',  'type' => 'expense', 'icon' => 'credit-card',     'color' => '#f43f5e'],
            ['name' => 'Pajak & Perizinan',   'type' => 'expense', 'icon' => 'receipt',         'color' => '#78716c'],
            ['name' => 'Pengeluaran Lainnya', 'type' => 'expense', 'icon' => 'dots-circle-horizontal', 'color' => '#94a3b8'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'user_id' => null],
                array_merge($category, ['is_default' => true])
            );
        }

        $this->command->info('Default categories seeded: ' . count($categories));
    }
}
