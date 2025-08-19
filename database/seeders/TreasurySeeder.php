<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Treasury;

class TreasurySeeder extends Seeder
{
    public function run(): void
    {
        Treasury::firstOrCreate(
            ['name' => 'الخزنة العامة'],
            [
                'opening_balance' => 0,
                'current_balance' => 0,
                'total_receipts' => 0,
                'total_payments' => 0,
                'description' => 'تم إنشاؤها تلقائياً',
                'is_active' => true,
            ]
        );
    }
}

