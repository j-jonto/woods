<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use App\Models\RevenueType;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\SupplierPayment;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Database\Seeder;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء أنواع المصروفات
        $expenseTypes = [
            ['name' => 'مصروفات إدارية', 'description' => 'المصروفات الإدارية العامة'],
            ['name' => 'مصروفات تشغيلية', 'description' => 'مصروفات التشغيل والإنتاج'],
            ['name' => 'مصروفات صيانة', 'description' => 'مصروفات الصيانة والإصلاح'],
            ['name' => 'مصروفات كهرباء', 'description' => 'مصروفات الكهرباء والطاقة'],
            ['name' => 'مصروفات إيجار', 'description' => 'مصروفات الإيجار والعقارات'],
        ];

        foreach ($expenseTypes as $type) {
            ExpenseType::firstOrCreate(['name' => $type['name']], $type);
        }

        // إنشاء أنواع الإيرادات
        $revenueTypes = [
            ['name' => 'إيرادات المبيعات', 'description' => 'إيرادات بيع المنتجات'],
            ['name' => 'إيرادات أخرى', 'description' => 'إيرادات متنوعة'],
            ['name' => 'إيرادات خدمات', 'description' => 'إيرادات الخدمات المقدمة'],
            ['name' => 'إيرادات استثمارية', 'description' => 'إيرادات الاستثمارات'],
        ];

        foreach ($revenueTypes as $type) {
            RevenueType::firstOrCreate(['name' => $type['name']], $type);
        }

        // إنشاء حسابات الصندوق والبنك
        $cashAccounts = [
            ['name' => 'الصندوق الرئيسي', 'type' => 'cash', 'balance' => 10000],
            ['name' => 'البنك الأهلي', 'type' => 'bank', 'balance' => 50000],
            ['name' => 'البنك التجاري', 'type' => 'bank', 'balance' => 25000],
            ['name' => 'صندوق الطوارئ', 'type' => 'cash', 'balance' => 5000],
        ];

        foreach ($cashAccounts as $account) {
            CashAccount::firstOrCreate(['name' => $account['name']], $account);
        }

        // إنشاء الخزنة العامة
        $treasury = Treasury::firstOrCreate(
            ['name' => 'الخزنة العامة'],
            [
                'name' => 'الخزنة العامة',
                'opening_balance' => 50000,
                'current_balance' => 50000,
                'description' => 'الخزنة العامة الرئيسية للمصنع',
                'is_active' => true
            ]
        );

        // إنشاء مصروفات تجريبية
        $expenses = [
            [
                'type_id' => ExpenseType::where('name', 'مصروفات إدارية')->first()->id,
                'amount' => 1500,
                'date' => '2024-01-15',
                'description' => 'مصروفات مكتبية',
                'reference_no' => 'EXP001'
            ],
            [
                'type_id' => ExpenseType::where('name', 'مصروفات تشغيلية')->first()->id,
                'amount' => 3000,
                'date' => '2024-01-20',
                'description' => 'مصروفات تشغيل الآلات',
                'reference_no' => 'EXP002'
            ],
        ];

        foreach ($expenses as $expense) {
            $expenseModel = Expense::firstOrCreate(['reference_no' => $expense['reference_no']], $expense);
            
            // إضافة معاملة صرف في الخزنة العامة
            if ($expenseModel->wasRecentlyCreated) {
                $treasury->addPayment(
                    $expense['amount'],
                    $expense['description'],
                    'expense',
                    $expenseModel->id
                );
            }
        }

        // إنشاء إيرادات تجريبية
        $revenues = [
            [
                'type_id' => RevenueType::where('name', 'إيرادات المبيعات')->first()->id,
                'amount' => 10000,
                'date' => '2024-01-10',
                'description' => 'مبيعات عصي المكانس',
                'reference_no' => 'REV001'
            ],
            [
                'type_id' => RevenueType::where('name', 'إيرادات أخرى')->first()->id,
                'amount' => 2000,
                'date' => '2024-01-25',
                'description' => 'إيرادات متنوعة',
                'reference_no' => 'REV002'
            ],
        ];

        foreach ($revenues as $revenue) {
            $revenueModel = Revenue::firstOrCreate(['reference_no' => $revenue['reference_no']], $revenue);
            
            // إضافة معاملة قبض في الخزنة العامة
            if ($revenueModel->wasRecentlyCreated) {
                $treasury->addReceipt(
                    $revenue['amount'],
                    $revenue['description'],
                    'revenue',
                    $revenueModel->id
                );
            }
        }

        // إضافة معاملات تجريبية مباشرة للخزنة العامة
        $treasury->addReceipt(5000, 'إيداع نقدي', null, null);
        $treasury->addPayment(2000, 'مصروفات تشغيلية', null, null);
        $treasury->addReceipt(8000, 'مدفوعات من العملاء', null, null);
        $treasury->addPayment(1500, 'مصروفات صيانة', null, null);

        $this->command->info('تم إنشاء البيانات المالية والخزنة العامة بنجاح!');
    }
}
