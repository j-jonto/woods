<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseType;
use App\Models\RevenueType;

class ExpenseRevenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // أنواع المصروفات
        $expenseTypes = [
            ['name' => 'مصروفات إدارية', 'code' => 'ADMIN', 'description' => 'المصروفات الإدارية العامة'],
            ['name' => 'مصروفات تشغيلية', 'code' => 'OPER', 'description' => 'مصروفات التشغيل اليومي'],
            ['name' => 'مصروفات صيانة', 'code' => 'MAINT', 'description' => 'مصروفات الصيانة والإصلاح'],
            ['name' => 'مصروفات كهرباء', 'code' => 'ELEC', 'description' => 'فواتير الكهرباء'],
            ['name' => 'مصروفات مياه', 'code' => 'WATER', 'description' => 'فواتير المياه'],
            ['name' => 'مصروفات إيجار', 'code' => 'RENT', 'description' => 'إيجار المباني والمستودعات'],
            ['name' => 'مصروفات رواتب', 'code' => 'SALARY', 'description' => 'رواتب الموظفين'],
            ['name' => 'مصروفات نقل', 'code' => 'TRANSP', 'description' => 'مصروفات النقل والشحن'],
            ['name' => 'مصروفات تسويق', 'code' => 'MARKET', 'description' => 'مصروفات التسويق والإعلان'],
            ['name' => 'مصروفات أخرى', 'code' => 'OTHER', 'description' => 'مصروفات متنوعة أخرى'],
        ];

        foreach ($expenseTypes as $expenseType) {
            ExpenseType::firstOrCreate(
                ['code' => $expenseType['code']],
                $expenseType
            );
        }

        // أنواع الإيرادات
        $revenueTypes = [
            ['name' => 'إيرادات المبيعات', 'code' => 'SALES', 'description' => 'إيرادات بيع المنتجات'],
            ['name' => 'إيرادات الخدمات', 'code' => 'SERVICES', 'description' => 'إيرادات تقديم الخدمات'],
            ['name' => 'إيرادات الإيجار', 'code' => 'RENTAL', 'description' => 'إيرادات إيجار الأصول'],
            ['name' => 'إيرادات الفوائد', 'code' => 'INTEREST', 'description' => 'إيرادات الفوائد البنكية'],
            ['name' => 'إيرادات الاستثمار', 'code' => 'INVEST', 'description' => 'إيرادات الاستثمارات'],
            ['name' => 'إيرادات العمولات', 'code' => 'COMM', 'description' => 'إيرادات العمولات'],
            ['name' => 'إيرادات أخرى', 'code' => 'OTHER', 'description' => 'إيرادات متنوعة أخرى'],
        ];

        foreach ($revenueTypes as $revenueType) {
            RevenueType::firstOrCreate(
                ['code' => $revenueType['code']],
                $revenueType
            );
        }

        $this->command->info('Expense and Revenue types seeded successfully!');
    }
} 