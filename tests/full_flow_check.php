<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\BillOfMaterial;
use App\Models\ProductionOrder;
use App\Models\ProductionMaterial;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\JournalEntryItem;
use App\Services\DoubleEntryService;
use App\Models\ChartOfAccount;

function out($msg) { echo $msg . "\n"; }

try {
    DB::transaction(function () {
        // Ensure warehouse
        $warehouse = Warehouse::firstOrCreate(['code' => 'WH-TST'], ['name' => 'مستودع اختبار']);

        // Items
        $cat = ItemCategory::firstOrCreate(['name' => 'اختبار'], ['description' => 'اختبار']);
        $raw = Item::firstOrCreate(
            ['code' => 'RM-001'],
            [
                'name' => 'مادة خام',
                'category_id' => $cat->id,
                'type' => 'raw_material',
                'unit_of_measure' => 'وحدة',
                'standard_cost' => 80.00,
                'selling_price' => 0,
                'is_active' => true,
            ]
        );
        $fg = Item::firstOrCreate(
            ['code' => 'FG-001'],
            [
                'name' => 'منتج نهائي',
                'category_id' => $cat->id,
                'type' => 'finished_goods',
                'unit_of_measure' => 'وحدة',
                'standard_cost' => 100.00,
                'selling_price' => 150.00,
                'is_active' => true,
            ]
        );

        // Seed initial raw material stock (receipt)
        InventoryTransaction::create([
            'transaction_date' => now(),
            'reference_no' => 'INIT-RM-001',
            'type' => 'receipt',
            'item_id' => $raw->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'unit_cost' => 80.00,
            'description' => 'رصيد افتتاحي مواد خام',
        ]);

        // Purchase Order (cash)
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-TST'], ['name' => 'مورد اختبار']);
        $po = PurchaseOrder::create([
            'order_no' => 'PO-TST-' . time(),
            'order_date' => now(),
            'supplier_id' => $supplier->id,
            'payment_type' => 'cash',
            'status' => 'received',
            'total_amount' => 800.00,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id' => $raw->id,
            'quantity' => 10,
            'unit_price' => 80.00,
            'amount' => 800.00,
        ]);
        // Create GL for purchase
        \App\Models\JournalEntry::createPurchaseEntry($po);

        // Production: BOM 2 x RM -> 1 x FG
        $bom = BillOfMaterial::firstOrCreate([
            'finished_good_id' => $fg->id,
            'raw_material_id' => $raw->id,
        ], [
            'quantity' => 2,
            'description' => '2xRM -> 1xFG',
            'is_active' => true,
        ]);

        $prod = ProductionOrder::create([
            'order_no' => 'PROD-' . time(),
            'item_id' => $fg->id,
            'bill_of_material_id' => $bom->id,
            'quantity' => 10,
            'order_date' => now(),
            'start_date' => now(),
            'status' => 'released',
        ]);

        // Issue raw materials per BOM (2 * 10 = 20)
        InventoryTransaction::create([
            'transaction_date' => now(),
            'reference_no' => 'ISS-RM-' . $prod->order_no,
            'type' => 'issue',
            'item_id' => $raw->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
            'unit_cost' => 80.00,
            'description' => 'صرف مواد خام للإنتاج',
        ]);

        // Receive finished goods (assume unit cost 160)
        InventoryTransaction::create([
            'transaction_date' => now(),
            'reference_no' => 'RCV-FG-' . $prod->order_no,
            'type' => 'receipt',
            'item_id' => $fg->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'unit_cost' => 160.00,
            'description' => 'استلام منتج نهائي من الإنتاج',
        ]);

        // Sales: cash and credit
        $customer = Customer::firstOrCreate(['code' => 'CUST-TST'], ['name' => 'عميل اختبار']);

        // Cash sale: 2 units at 150 each
        $soCash = SalesOrder::create([
            'order_no' => 'SO-CASH-' . time(),
            'order_date' => now(),
            'customer_id' => $customer->id,
            'payment_type' => 'cash',
            'status' => 'confirmed',
            'total_amount' => 300.00,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $soCash->id,
            'item_id' => $fg->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'amount' => 300.00,
        ]);
        \App\Models\JournalEntry::createSalesEntry($soCash);

        // Credit sale: 1 unit at 150
        $soCr = SalesOrder::create([
            'order_no' => 'SO-CR-' . time(),
            'order_date' => now(),
            'customer_id' => $customer->id,
            'payment_type' => 'credit',
            'status' => 'confirmed',
            'total_amount' => 150.00,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $soCr->id,
            'item_id' => $fg->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'amount' => 150.00,
        ]);
        \App\Models\JournalEntry::createSalesEntry($soCr);

        // Treasury ops: generic cash receipt/payment
        DoubleEntryService::createCashReceiptEntry(200, 'قبض نقدي عام');
        DoubleEntryService::createCashPaymentEntry(120, 'صرف نقدي عام');
    });

    // Summaries
    out("=== Summary: Debits vs Credits ===");
    $posted = JournalEntryItem::whereHas('journalEntry', fn($q) => $q->where('status', 'posted'));
    $td = (clone $posted)->sum('debit');
    $tc = (clone $posted)->sum('credit');
    out('Total Debits:  ' . number_format($td, 2));
    out('Total Credits: ' . number_format($tc, 2));
    out($td == $tc ? 'OK: Balanced' : 'ERROR: Not balanced');

    // Key accounts
    $codes = ['10100','10200','10300','10500','20100','40100','50100'];
    foreach ($codes as $code) {
        $acc = ChartOfAccount::where('code', $code)->first();
        if (!$acc) { continue; }
        $bal = DoubleEntryService::getAccountBalance($acc->id);
        out(sprintf("%s: %0.2f", $code, $bal));
    }

    out("Done.");

} catch (Exception $e) {
    out('ERROR: ' . $e->getMessage());
    out($e->getTraceAsString());
    exit(1);
}

