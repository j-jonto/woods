<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            CurrencySeeder::class,
            ChartOfAccountsSeeder::class,
            ReferenceDataSeeder::class,
            ExpenseRevenueSeeder::class,
            TreasurySeeder::class,
            // DemoDataSeeder::class, // مؤقتاً: تعطيل بيانات الديمو لتفادي تعارضات المخطط أثناء الاختبارات المحاسبية
        ]);
    }
} 