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
use Illuminate\Support\Facades\DB;

echo "=== اختبار استلام البضاعة ===\n\n";

try {
    // التحقق من وجود البيانات الأساسية
    $supplier = Supplier::first();
    $item = Item::first();
    $warehouse = Warehouse::first();

    if (!$supplier) {
        echo "❌ لا يوجد موردين في النظام\n";
        exit(1);
    }

    if (!$item) {
        echo "❌ لا توجد منتجات في النظام\n";
        exit(1);
    }

    if (!$warehouse) {
        echo "❌ لا توجد مستودعات في النظام\n";
        exit(1);
    }

    echo "1. معلومات المورد: {$supplier->name}\n";
    echo "2. معلومات المنتج: {$item->name}\n";
    echo "3. معلومات المستودع: {$warehouse->name}\n\n";

    // إنشاء طلب شراء
    echo "4. إنشاء طلب شراء...\n";
    $purchaseOrder = PurchaseOrder::create([
        'order_no' => 'PO-' . date('Ymd') . '-001',
        'order_date' => now(),
        'supplier_id' => $supplier->id,
        'total_amount' => 10000.00,
        'status' => 'pending',
        'notes' => 'طلب شراء اختبار',
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

    // استلام البضاعة
    echo "5. استلام البضاعة...\n";
    
    DB::transaction(function () use ($purchaseOrder, $warehouse) {
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
        }

        // تحديث حالة طلب الشراء
        $purchaseOrder->update(['status' => 'received']);
    });

    echo "   ✓ تم استلام البضاعة بنجاح\n";
    echo "   ✓ تم إضافة المشتريات لحساب المورد\n";
    echo "   ✓ تم إضافة البضاعة للمخزون\n\n";

    // عرض النتائج
    echo "6. النتائج:\n";
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