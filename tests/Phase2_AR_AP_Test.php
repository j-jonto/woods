<?php

// Bootstrap Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use Illuminate\Http\Request;

function test_log($message) {
    echo "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
}

DB::beginTransaction();

try {
    test_log("Starting AR/AP Balance Test...");

    // 1. Setup basic data
    $warehouse = Warehouse::firstOrCreate(['code' => 'WH-TST'], ['name' => 'Test Warehouse']);
    $category = ItemCategory::firstOrCreate(['name' => 'Test Category']);
    $testItem = Item::firstOrCreate(
        ['code' => 'ITEM-TEST-001'],
        [
            'name' => 'Test Item',
            'category_id' => $category->id,
            'type' => 'finished_goods',
            'unit_of_measure' => 'unit',
            'standard_cost' => 50.00,
            'selling_price' => 100.00,
            'is_active' => true,
        ]
    );

    // 2. Test Accounts Payable (Credit Purchase)
    test_log("--- Testing Accounts Payable ---");
    $supplier = Supplier::create([
        'code' => 'SUP-TEST-AP',
        'name' => 'AP Test Supplier',
        'current_balance' => 0,
    ]);
    $purchaseOrder = PurchaseOrder::create([
        'order_no' => 'PO-TEST-' . time(),
        'order_date' => now(),
        'supplier_id' => $supplier->id,
        'payment_type' => 'credit',
        'status' => 'ordered', // Must be 'ordered' to be received
        'total_amount' => 200.00,
    ]);
    PurchaseOrderItem::create([
        'purchase_order_id' => $purchaseOrder->id,
        'item_id' => $testItem->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'amount' => 200.00,
    ]);

    // Simulate controller action
    $poController = new PurchaseOrderController();
    $poController->receive($purchaseOrder->id);

    $supplier->refresh();
    test_log("Supplier balance after credit purchase: " . $supplier->current_balance);
    if ($supplier->current_balance != 200.00) {
        throw new Exception("AP Test Failed: Supplier balance is incorrect. Expected 200.00, got " . $supplier->current_balance);
    }

    $supplierPayment = SupplierPayment::where('notes', 'like', '%'.$purchaseOrder->order_no.'%')->first();
    if (!$supplierPayment || $supplierPayment->payment_method !== 'invoice') {
         throw new Exception("AP Test Failed: Supplier payment record is incorrect or missing. Method: " . ($supplierPayment->payment_method ?? 'N/A'));
    }
    test_log("AP Test Passed!");

    // 3. Test Accounts Receivable (Credit Sale)
    test_log("--- Testing Accounts Receivable ---");
    $customer = Customer::create([
        'code' => 'CUST-TEST-AR',
        'name' => 'AR Test Customer',
        'current_balance' => 0,
        'credit_limit' => 1000,
    ]);
    $salesOrder = SalesOrder::create([
        'order_no' => 'SO-TEST-' . time(),
        'order_date' => now(),
        'customer_id' => $customer->id,
        'payment_type' => 'credit',
        'status' => 'draft',
        'total_amount' => 150.00,
    ]);
    SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'item_id' => $testItem->id,
        'quantity' => 1,
        'unit_price' => 150.00,
        'amount' => 150.00,
    ]);

    // Simulate controller action
    $soController = new SalesOrderController();
    $request = new Request(['status' => 'confirmed']);
    $soController->updateStatus($request, $salesOrder);

    $customer->refresh();
    test_log("Customer balance after credit sale: " . $customer->current_balance);
    if ($customer->current_balance != 150.00) {
        throw new Exception("AR Test Failed: Customer balance is incorrect. Expected 150.00, got " . $customer->current_balance);
    }

    $customerPayment = Payment::where('notes', 'like', '%'.$salesOrder->order_no.'%')->first();
    if (!$customerPayment || $customerPayment->type !== 'invoice') {
        throw new Exception("AR Test Failed: Customer payment record is incorrect or missing. Type: " . ($customerPayment->type ?? 'N/A'));
    }
    test_log("AR Test Passed!");

    // 4. Test Accounts Receivable with Zero Credit Limit
    test_log("--- Testing AR with Zero Credit Limit ---");
    $zeroLimitCustomer = Customer::create([
        'code' => 'CUST-TEST-ZERO',
        'name' => 'Zero Limit Test Customer',
        'credit_limit' => 0, // Explicitly set to 0, which is the default
    ]);
    $salesOrderZero = SalesOrder::create([
        'order_no' => 'SO-TEST-ZERO-' . time(),
        'order_date' => now(),
        'customer_id' => $zeroLimitCustomer->id,
        'payment_type' => 'credit',
        'status' => 'draft',
        'total_amount' => 50.00,
    ]);
    SalesOrderItem::create([
        'sales_order_id' => $salesOrderZero->id,
        'item_id' => $testItem->id,
        'quantity' => 1,
        'unit_price' => 50.00,
        'amount' => 50.00,
    ]);

    // This should now pass without a credit limit exception
    $soController->updateStatus(new Request(['status' => 'confirmed']), $salesOrderZero);

    $zeroLimitCustomer->refresh();
    test_log("Zero Limit Customer balance after credit sale: " . $zeroLimitCustomer->current_balance);
    if ($zeroLimitCustomer->current_balance != 50.00) {
        throw new Exception("AR Zero Limit Test Failed: Customer balance is incorrect. Expected 50.00, got " . $zeroLimitCustomer->current_balance);
    }
    test_log("AR Zero Limit Test Passed!");


    test_log("All tests passed successfully!");

} catch (Exception $e) {
    test_log("!!! TEST FAILED !!!");
    test_log("Error: " . $e->getMessage());
    test_log("Stack Trace: " . $e->getTraceAsString());
    DB::rollBack();
    exit(1);
}

DB::rollBack();
test_log("Transaction rolled back. No changes were saved to the database.");
exit(0);
