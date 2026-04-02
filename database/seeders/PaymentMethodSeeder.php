<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'QRIS',
                'description' => 'Scan QRIS dengan aplikasi pembayaran apapun',
                'icon' => null,
                'placeholder' => 'Scan kode QRIS di bawah',
                'display_number' => null,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Dana',
                'description' => 'Dompet digital DANA',
                'icon' => null,
                'placeholder' => 'Contoh nomor Dana: 08xx xxxx xxxx',
                'display_number' => '0812 3456 7890',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'OVO',
                'description' => 'Dompet digital OVO',
                'icon' => null,
                'placeholder' => 'Contoh nomor OVO: 08xx xxxx xxxx',
                'display_number' => '0821 9876 5432',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'GCash',
                'description' => 'Digital wallet GCash (Philippines)',
                'icon' => null,
                'placeholder' => 'Contoh nomor GCash: 09xx xxxx xxxx',
                'display_number' => '0919 1234 5678',
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Transfer Bank',
                'description' => 'Transfer langsung ke rekening bank',
                'icon' => null,
                'placeholder' => 'Nomor rekening akan ditampilkan setelah konfirmasi',
                'display_number' => null,
                'display_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}

