<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Services\DoubleEntryService;
use Illuminate\Support\Facades\DB;

echo "=== اختبار استلام البضاعة مع القيود المحاسبية ===\n\n";

try {
    // إنشاء البيانات الأساسية
    echo "1. إنشاء البيانات الأساسية...\n";
    
    $category = ItemCategory::firstOrCreate(
        ['name' => 'المواد الخام'],
        ['description' => 'المواد الخام للإنتاج']
    );
    echo "   ✓ فئة المنتجات: {$category->name}\n";

    $item = Item::firstOrCreate(
        ['name' => 'خشب زان طبيعي'],
        [
            'code' => 'WOOD001',
            'category_id' => $category->id,
            'type' => 'raw_material',
            'unit_of_measure' => 'متر مكعب',
            'standard_cost' => 1000.00,
            'selling_price' => 1200.00,
            'reorder_point' => 5,
            'is_active' => true,
        ]
    );
    echo "   ✓ المنتج: {$item->name}\n";

    $supplier = Supplier::firstOrCreate(
        ['name' => 'شركة الأخشاب المتحدة'],
        [
            'code' => 'SUP001',
            'contact_person' => 'أحمد محمد',
            'phone' => '0501234567',
            'email' => 'info@woodsupplier.com',
            'address' => 'الرياض، المملكة العربية السعودية',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'total_payables' => 0.00,
            'total_payments' => 0.00,
            'credit_limit' => 50000.00,
            'is_active' => true,
        ]
    );
    echo "   ✓ المورد: {$supplier->name}\n";

    $warehouse = Warehouse::firstOrCreate(
        ['code' => 'WH001'],
        [
            'name' => 'المستودع الرئيسي',
            'location' => 'الرياض',
            'is_active' => true,
        ]
    );
    echo "   ✓ المستودع: {$warehouse->name}\n\n";

    // إنشاء طلب شراء
    echo "2. إنشاء طلب شراء...\n";
    $purchaseOrder = PurchaseOrder::create([
        'order_no' => 'PO-' . date('Ymd') . '-002',
        'order_date' => now(),
        'supplier_id' => $supplier->id,
        'total_amount' => 10000.00,
        'status' => 'draft',
        'created_by' => 1,
    ]);

    echo "   ✓ تم إنشاء طلب الشراء: {$purchaseOrder->order_no}\n";

    // إضافة منتج لطلب الشراء
    $orderItem = PurchaseOrderItem::create([
        'purchase_order_id' => $purchaseOrder->id,
        'item_id' => $item->id,
        'quantity' => 10,
        'unit_price' => 1000.00,
        'amount' => 10000.00,
    ]);

    echo "   ✓ تم إضافة المنتج لطلب الشراء\n\n";

    // استلام البضاعة مع القيود المحاسبية
    echo "3. استلام البضاعة مع القيود المحاسبية...\n";
    
    DB::transaction(function () use ($purchaseOrder, $warehouse, $item) {
        // إضافة المشتريات لحساب المورد
        $purchaseOrder->supplier->addPayable(
            $purchaseOrder->total_amount,
            'مشتريات آجلة - طلب رقم: ' . $purchaseOrder->order_no,
            'purchase_order',
            $purchaseOrder->id
        );

        // إضافة البضاعة للمخزون
        foreach ($purchaseOrder->items as $orderItem) {
            InventoryTransaction::create([
                'item_id' => $orderItem->item_id,
                'warehouse_id' => $warehouse->id,
                'type' => 'receipt',
                'quantity' => $orderItem->quantity,
                'unit_cost' => $orderItem->unit_price,
                'reference_no' => 'PO-RCV-' . $purchaseOrder->order_no . '-' . $orderItem->id,
                'reference_type' => 'purchase_order',
                'reference_id' => $purchaseOrder->id,
                'transaction_date' => now(),
                'description' => 'استلام بضاعة من طلب شراء: ' . $purchaseOrder->order_no,
                'created_by' => 1,
            ]);

            // إنشاء قيد محاسبي للمخزون
            DoubleEntryService::createInventoryAdjustmentEntry(
                $item,
                $orderItem->quantity,
                $orderItem->unit_price,
                'increase',
                'استلام بضاعة من طلب شراء: ' . $purchaseOrder->order_no
            );
        }

        // تحديث حالة طلب الشراء
        $purchaseOrder->update(['status' => 'received']);
    });

    echo "   ✓ تم استلام البضاعة بنجاح\n";
    echo "   ✓ تم إضافة المشتريات لحساب المورد\n";
    echo "   ✓ تم إضافة البضاعة للمخزون\n";
    echo "   ✓ تم إنشاء القيود المحاسبية\n\n";

    // عرض النتائج
    echo "4. النتائج:\n";
    echo "   - رصيد المورد: {$purchaseOrder->supplier->current_balance} ريال\n";
    echo "   - مخزون المنتج: {$item->getCurrentStock()}\n";
    echo "   - حالة طلب الشراء: {$purchaseOrder->status}\n";

    echo "\n=== انتهى الاختبار بنجاح ===\n";

} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}

// حذف الملف بعد الانتهاء
unlink(__FILE__);
echo "تم حذف ملف الاختبار تلقائياً.\n"; 