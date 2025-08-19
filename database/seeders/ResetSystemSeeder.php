<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 بدء إعادة إنشاء البيانات الأساسية...\n\n";

        // 1. إنشاء الخزنة العامة
        DB::table('treasury')->insert([
            'name' => 'الخزنة العامة',
            'opening_balance' => 100000,
            'current_balance' => 100000,
            'description' => 'الخزنة العامة للشركة',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ تم إنشاء الخزنة العامة\n";

        // 2. إنشاء المستودع الرئيسي
        DB::table('warehouses')->insert([
            'code' => 'WH001',
            'name' => 'المستودع الرئيسي',
            'location' => 'المقر الرئيسي',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ تم إنشاء المستودع الرئيسي\n";

        // 3. إنشاء مركز العمل الرئيسي
        DB::table('work_centers')->insert([
            'code' => 'WC001',
            'name' => 'مركز الإنتاج الرئيسي',
            'description' => 'مركز الإنتاج الرئيسي للشركة',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ تم إنشاء مركز العمل الرئيسي\n";

        // 4. إنشاء تصنيفات الأصناف الأساسية
        $categories = [
            ['name' => 'المواد الخام', 'description' => 'المواد الخام للإنتاج'],
            ['name' => 'المنتجات النهائية', 'description' => 'المنتجات النهائية للبيع'],
            ['name' => 'المنتجات قيد الإنتاج', 'description' => 'المنتجات في مرحلة الإنتاج'],
        ];

        foreach ($categories as $category) {
            DB::table('item_categories')->insert([
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✓ تم إنشاء تصنيفات الأصناف\n";

        // 5. إنشاء أنواع المصروفات الأساسية
        $expenseTypes = [
            ['name' => 'مصاريف إدارية', 'description' => 'المصاريف الإدارية العامة'],
            ['name' => 'مصاريف تشغيلية', 'description' => 'مصاريف التشغيل والإنتاج'],
            ['name' => 'مصاريف تسويقية', 'description' => 'مصاريف التسويق والمبيعات'],
        ];

        foreach ($expenseTypes as $type) {
            DB::table('expense_types')->insert([
                'name' => $type['name'],
                'description' => $type['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✓ تم إنشاء أنواع المصروفات\n";

        // 6. إنشاء أنواع الإيرادات الأساسية
        $revenueTypes = [
            ['name' => 'إيرادات المبيعات', 'description' => 'إيرادات بيع المنتجات'],
            ['name' => 'إيرادات خدمات', 'description' => 'إيرادات الخدمات المقدمة'],
            ['name' => 'إيرادات أخرى', 'description' => 'إيرادات أخرى متنوعة'],
        ];

        foreach ($revenueTypes as $type) {
            DB::table('revenue_types')->insert([
                'name' => $type['name'],
                'description' => $type['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✓ تم إنشاء أنواع الإيرادات\n";

        // 7. إنشاء تصنيفات الأصول
        $assetCategories = [
            ['name' => 'المباني', 'description' => 'المباني والعقارات'],
            ['name' => 'الآلات والمعدات', 'description' => 'الآلات والمعدات الإنتاجية'],
            ['name' => 'الأثاث', 'description' => 'الأثاث والمفروشات'],
            ['name' => 'السيارات', 'description' => 'السيارات والمركبات'],
        ];

        foreach ($assetCategories as $category) {
            DB::table('asset_categories')->insert([
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✓ تم إنشاء تصنيفات الأصول\n";

        // 8. إنشاء دليل الحسابات الأساسي
        $accounts = [
            ['code' => '1000', 'name' => 'الأصول المتداولة', 'type' => 'asset', 'parent_id' => null],
            ['code' => '1100', 'name' => 'النقد وما في حكمه', 'type' => 'asset', 'parent_id' => 1],
            ['code' => '1200', 'name' => 'المخزون', 'type' => 'asset', 'parent_id' => 1],
            ['code' => '2000', 'name' => 'الخصوم المتداولة', 'type' => 'liability', 'parent_id' => null],
            ['code' => '2100', 'name' => 'المدينون', 'type' => 'liability', 'parent_id' => 4],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => 'equity', 'parent_id' => null],
            ['code' => '3100', 'name' => 'رأس المال', 'type' => 'equity', 'parent_id' => 6],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => 'revenue', 'parent_id' => null],
            ['code' => '4100', 'name' => 'إيرادات المبيعات', 'type' => 'revenue', 'parent_id' => 8],
            ['code' => '5000', 'name' => 'المصروفات', 'type' => 'expense', 'parent_id' => null],
            ['code' => '5100', 'name' => 'مصاريف البضاعة المباعة', 'type' => 'expense', 'parent_id' => 10],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->insert([
                'code' => $account['code'],
                'name' => $account['name'],
                'type' => $account['type'],
                'parent_id' => $account['parent_id'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✓ تم إنشاء دليل الحسابات\n";

        // 9. إنشاء حساب الصندوق
        DB::table('cash_accounts')->insert([
            'code' => 'CA001',
            'name' => 'الصندوق الرئيسي',
            'type' => 'cash',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'description' => 'الصندوق الرئيسي للشركة',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ تم إنشاء حساب الصندوق\n";

        // 10. إنشاء حساب البنك
        DB::table('cash_accounts')->insert([
            'code' => 'BA001',
            'name' => 'الحساب البنكي الرئيسي',
            'type' => 'bank',
            'opening_balance' => 50000,
            'current_balance' => 50000,
            'description' => 'الحساب البنكي الرئيسي للشركة',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ تم إنشاء حساب البنك\n";

        echo "\n🎉 تم إعادة إنشاء البيانات الأساسية بنجاح!\n";
        echo "📝 النظام جاهز للاستخدام مع البيانات الجديدة.\n";
        echo "\n💡 نصائح للبدء:\n";
        echo "1. أضف الموردين والعملاء\n";
        echo "2. أضف الأصناف والمواد\n";
        echo "3. أنشئ أوامر الشراء والبيع\n";
        echo "4. اختبر الربط التلقائي بين الأنظمة\n";
    }
} 