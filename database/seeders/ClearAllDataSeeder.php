<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تعطيل فحص المفاتيح الأجنبية مؤقتاً (متوافق مع مختلف السواقات)
        Schema::disableForeignKeyConstraints();

        // دالة مساعدة لحذف جدول بأمان إذا كان موجوداً
        $safeTruncate = function (string $table, string $message) {
            if (Schema::hasTable($table)) {
                try {
                    DB::table($table)->truncate();
                } catch (\Throwable $e) {
                    // بعض محركات قواعد البيانات لا تدعم truncate مع القيود؛ نستخدم delete
                    DB::table($table)->delete();
                }
                echo "✓ {$message}\n";
            }
        };

        // حذف البيانات من الجداول بالترتيب الصحيح (من الأحدث إلى الأقدم)
        
        // 1. حذف معاملات الخزنة العامة
        $safeTruncate('treasury_transactions', 'تم حذف معاملات الخزنة العامة');
        
        // 2. حذف مدفوعات الموردين
        $safeTruncate('supplier_payments', 'تم حذف مدفوعات الموردين');
        
        // 3. حذف معاملات المخزون
        $safeTruncate('inventory_transactions', 'تم حذف معاملات المخزون');
        
        // 4. حذف مواد الإنتاج
        $safeTruncate('production_materials', 'تم حذف مواد الإنتاج');
        
        // 5. حذف أوامر الإنتاج
        $safeTruncate('production_orders', 'تم حذف أوامر الإنتاج');
        
        // 6. حذف قوائم المواد
        $safeTruncate('bill_of_materials', 'تم حذف قوائم المواد');
        
        // 7. حذف عناصر فواتير الشراء
        $safeTruncate('purchase_invoice_items', 'تم حذف عناصر فواتير الشراء');
        
        // 8. حذف فواتير الشراء
        $safeTruncate('purchase_invoices', 'تم حذف فواتير الشراء');
        
        // 9. حذف عناصر أوامر الشراء
        $safeTruncate('purchase_order_items', 'تم حذف عناصر أوامر الشراء');
        
        // 10. حذف أوامر الشراء
        $safeTruncate('purchase_orders', 'تم حذف أوامر الشراء');
        
        // 11. حذف عناصر أوامر البيع
        $safeTruncate('sales_order_items', 'تم حذف عناصر أوامر البيع');
        
        // 12. حذف أوامر البيع
        $safeTruncate('sales_orders', 'تم حذف أوامر البيع');
        
        // 13. حذف عناصر القيود المحاسبية
        $safeTruncate('journal_entry_items', 'تم حذف عناصر القيود المحاسبية');
        
        // 14. حذف القيود المحاسبية
        $safeTruncate('journal_entries', 'تم حذف القيود المحاسبية');
        
        // 15. حذف سندات القبض
        $safeTruncate('receipt_vouchers', 'تم حذف سندات القبض');
        
        // 16. حذف سندات الصرف
        $safeTruncate('payment_vouchers', 'تم حذف سندات الصرف');
        
        // 17. حذف المصروفات
        $safeTruncate('expenses', 'تم حذف المصروفات');
        
        // 18. حذف الإيرادات
        $safeTruncate('revenues', 'تم حذف الإيرادات');
        
        // 19. حذف المدفوعات
        $safeTruncate('payments', 'تم حذف المدفوعات');
        
        // 20. حذف الأصول الثابتة
        $safeTruncate('fixed_assets', 'تم حذف الأصول الثابتة');
        
        // 21. حذف جرد الأصول
        $safeTruncate('asset_inventories', 'تم حذف جرد الأصول');
        
        // 22. حذف إهلاك الأصول
        $safeTruncate('asset_depreciations', 'تم حذف إهلاك الأصول');
        
        // 23. حذف معاملات الممثلين
        $safeTruncate('representative_transactions', 'تم حذف معاملات الممثلين');
        
        // 24. حذف سجلات التدقيق
        $safeTruncate('audit_logs', 'تم حذف سجلات التدقيق');
        
        // 25. حذف الأصناف
        $safeTruncate('items', 'تم حذف الأصناف');
        
        // 26. حذف تصنيفات الأصناف
        $safeTruncate('item_categories', 'تم حذف تصنيفات الأصناف');
        
        // 27. حذف المستودعات
        $safeTruncate('warehouses', 'تم حذف المستودعات');
        
        // 28. حذف مراكز العمل
        $safeTruncate('work_centers', 'تم حذف مراكز العمل');
        
        // 29. حذف العملاء
        $safeTruncate('customers', 'تم حذف العملاء');
        
        // 30. حذف الموردين
        $safeTruncate('suppliers', 'تم حذف الموردين');
        
        // 31. حذف الممثلين
        $safeTruncate('sales_representatives', 'تم حذف الممثلين');
        
        // 32. حذف تصنيفات الأصول
        $safeTruncate('asset_categories', 'تم حذف تصنيفات الأصول');
        
        // 33. حذف أنواع المصروفات
        $safeTruncate('expense_types', 'تم حذف أنواع المصروفات');
        
        // 34. حذف أنواع الإيرادات
        $safeTruncate('revenue_types', 'تم حذف أنواع الإيرادات');
        
        // 35. حذف حسابات الصندوق والبنك
        $safeTruncate('cash_accounts', 'تم حذف حسابات الصندوق والبنك');
        
        // 36. حذف دليل الحسابات
        $safeTruncate('chart_of_accounts', 'تم حذف دليل الحسابات');
        
        // 37. حذف الخزنة العامة
        $safeTruncate('treasury', 'تم حذف الخزنة العامة');
        
        // 38. حذف المستخدمين (باستثناء المدير)
        if (Schema::hasTable('users')) {
            DB::table('users')->where('id', '!=', 1)->delete();
            echo "✓ تم حذف المستخدمين (باستثناء المدير)\n";
        }
        
        // إعادة تفعيل فحص المفاتيح الأجنبية
        Schema::enableForeignKeyConstraints();
        
        echo "\n🎉 تم حذف جميع البيانات بنجاح!\n";
        echo "📝 يمكنك الآن البدء في إدخال بيانات جديدة.\n";
    }
} 