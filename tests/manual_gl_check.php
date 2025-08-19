<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Services\DoubleEntryService;
use App\Models\ChartOfAccount;

function out($msg) { echo $msg . "\n"; }

try {
    DB::transaction(function () {
        // Base data
        $category = ItemCategory::firstOrCreate(['name' => 'مواد'], ['description' => 'مواد اختبار']);
        $item = Item::firstOrCreate(
            ['code' => 'ITEM-001'],
            [
                'name' => 'صنف اختبار',
                'category_id' => $category->id,
                'type' => 'finished_goods',
                'unit_of_measure' => 'قطعة',
                'standard_cost' => 100.00,
                'selling_price' => 150.00,
                'is_active' => true,
            ]
        );

        $customer = Customer::firstOrCreate(['code' => 'CUST-001'], ['name' => 'عميل اختبار']);
        $supplier = Supplier::firstOrCreate(['code' => 'SUP-001'], ['name' => 'مورد اختبار']);

        // Cash Sale
        $sale = SalesOrder::create([
            'order_no' => 'SO-' . time(),
            'order_date' => now(),
            'customer_id' => $customer->id,
            'payment_type' => 'cash',
            'status' => 'confirmed',
            'total_amount' => 300.00,
            'created_by' => null,
        ]);
        SalesOrderItem::create([
            'sales_order_id' => $sale->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'amount' => 300.00,
        ]);
        JournalEntry::createSalesEntry($sale);

        // Cash Purchase
        $purchase = PurchaseOrder::create([
            'order_no' => 'PO-' . time(),
            'order_date' => now(),
            'supplier_id' => $supplier->id,
            'payment_type' => 'cash',
            'status' => 'received',
            'total_amount' => 160.00,
            'created_by' => null,
        ]);
        PurchaseOrderItem::create([
            'purchase_order_id' => $purchase->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'unit_price' => 80.00,
            'amount' => 160.00,
        ]);
        JournalEntry::createPurchaseEntry($purchase);

        // Cash receipt and payment
        DoubleEntryService::createCashReceiptEntry(50, 'اختبار قبض');
        DoubleEntryService::createCashPaymentEntry(30, 'اختبار صرف');
    });

    // Report balances
    $accounts = [
        'Cash 10100' => '10100',
        'AR 10200' => '10200',
        'Inventory 10300' => '10300',
        'Finished Goods 10500' => '10500',
        'AP 20100' => '20100',
        'Sales 40100' => '40100',
        'COGS 50100' => '50100',
    ];

    out("=== Account Balances (as of now) ===");
    foreach ($accounts as $label => $code) {
        $acc = ChartOfAccount::where('code', $code)->first();
        if ($acc) {
            $bal = DoubleEntryService::getAccountBalance($acc->id);
            out(sprintf("%-20s (%s): %0.2f", $label, $code, $bal));
        } else {
            out(sprintf("%-20s (%s): not found", $label, $code));
        }
    }

    // Check total debits = credits for posted
    $postedItemQuery = JournalEntryItem::whereHas('journalEntry', function ($q) { $q->where('status', 'posted'); });
    $totalDebit = (clone $postedItemQuery)->sum('debit');
    $totalCredit = (clone $postedItemQuery)->sum('credit');

    out("\nTotal Posted Debits:  " . number_format($totalDebit, 2));
    out("Total Posted Credits: " . number_format($totalCredit, 2));
    out($totalDebit == $totalCredit ? "OK: Balanced" : "ERROR: Not balanced");

} catch (Exception $e) {
    out('ERROR: ' . $e->getMessage());
    out($e->getTraceAsString());
    exit(1);
}

out("\nDone.");

