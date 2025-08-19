<?php

namespace App\Http\Controllers;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\CashAccount;
use App\Models\SupplierPayment;
use App\Models\ReceiptVoucher;
use App\Models\PaymentVoucher;
use App\Models\PurchaseInvoice;
use App\Models\SalesOrder;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrintController extends Controller
{
    /**
     * طباعة تقرير الخزنة العامة
     */
    public function treasuryReport(Request $request)
    {
        $treasury = Treasury::first();
        $from_date = $request->get('from_date', now()->startOfMonth());
        $to_date = $request->get('to_date', now()->endOfMonth());
        
        $transactions = TreasuryTransaction::with(['treasury', 'creator'])
            ->when($from_date, function($q) use ($from_date) {
                $q->whereDate('transaction_date', '>=', $from_date);
            })
            ->when($to_date, function($q) use ($to_date) {
                $q->whereDate('transaction_date', '<=', $to_date);
            })
            ->orderByDesc('transaction_date')
            ->get();
            
        return view('prints.treasury_report', compact('treasury', 'transactions', 'from_date', 'to_date'));
    }

    /**
     * طباعة إيصال قبض
     */
    public function receiptVoucher(ReceiptVoucher $receiptVoucher)
    {
        return view('prints.receipt_voucher', compact('receiptVoucher'));
    }

    /**
     * طباعة سند صرف
     */
    public function paymentVoucher(PaymentVoucher $paymentVoucher)
    {
        return view('prints.payment_voucher', compact('paymentVoucher'));
    }

    /**
     * طباعة فاتورة شراء
     */
    public function purchaseInvoice(PurchaseInvoice $purchaseInvoice)
    {
        return view('prints.purchase_invoice', compact('purchaseInvoice'));
    }

    /**
     * طباعة فاتورة مبيعات
     */
    public function salesInvoice(SalesOrder $salesOrder)
    {
        return view('prints.sales_invoice', compact('salesOrder'));
    }

    /**
     * طباعة تقرير المصروفات
     */
    public function expensesReport(Request $request)
    {
        $from_date = $request->get('from_date', now()->startOfMonth());
        $to_date = $request->get('to_date', now()->endOfMonth());
        $type_id = $request->get('type_id');
        
        $expenses = Expense::with(['type', 'creator'])
            ->when($from_date, function($q) use ($from_date) {
                $q->whereDate('date', '>=', $from_date);
            })
            ->when($to_date, function($q) use ($to_date) {
                $q->whereDate('date', '<=', $to_date);
            })
            ->when($type_id, function($q) use ($type_id) {
                $q->where('type_id', $type_id);
            })
            ->orderByDesc('date')
            ->get();
            
        return view('prints.expenses_report', compact('expenses', 'from_date', 'to_date'));
    }

    /**
     * طباعة تقرير الإيرادات
     */
    public function revenuesReport(Request $request)
    {
        $from_date = $request->get('from_date', now()->startOfMonth());
        $to_date = $request->get('to_date', now()->endOfMonth());
        $type_id = $request->get('type_id');
        
        $revenues = Revenue::with(['type', 'creator'])
            ->when($from_date, function($q) use ($from_date) {
                $q->whereDate('date', '>=', $from_date);
            })
            ->when($to_date, function($q) use ($to_date) {
                $q->whereDate('date', '<=', $to_date);
            })
            ->when($type_id, function($q) use ($type_id) {
                $q->where('type_id', $type_id);
            })
            ->orderByDesc('date')
            ->get();
            
        return view('prints.revenues_report', compact('revenues', 'from_date', 'to_date'));
    }

    /**
     * طباعة تقرير المخزون
     */
    public function inventoryReport(Request $request)
    {
        $warehouse_id = $request->get('warehouse_id');
        
        $stock = InventoryTransaction::selectRaw('item_id, warehouse_id, SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) as quantity')
            ->with(['item', 'warehouse'])
            ->when($warehouse_id, function($q) use ($warehouse_id) {
                $q->where('warehouse_id', $warehouse_id);
            })
            ->groupBy('item_id', 'warehouse_id')
            ->having('quantity', '>', 0)
            ->get();
            
        return view('prints.inventory_report', compact('stock'));
    }

    /**
     * طباعة تقرير المبيعات
     */
    public function salesReport(Request $request)
    {
        $from_date = $request->get('from_date', now()->startOfMonth());
        $to_date = $request->get('to_date', now()->endOfMonth());
        $customer_id = $request->get('customer_id');
        
        $sales = SalesOrder::with(['customer', 'items'])
            ->when($from_date, function($q) use ($from_date) {
                $q->whereDate('order_date', '>=', $from_date);
            })
            ->when($to_date, function($q) use ($to_date) {
                $q->whereDate('order_date', '<=', $to_date);
            })
            ->when($customer_id, function($q) use ($customer_id) {
                $q->where('customer_id', $customer_id);
            })
            ->orderByDesc('order_date')
            ->get();
            
        return view('prints.sales_report', compact('sales', 'from_date', 'to_date'));
    }

    /**
     * طباعة تقرير مدفوعات الموردين
     */
    public function supplierPaymentsReport(Request $request)
    {
        $from_date = $request->get('from_date', now()->startOfMonth());
        $to_date = $request->get('to_date', now()->endOfMonth());
        $supplier_id = $request->get('supplier_id');
        
        $payments = SupplierPayment::with(['supplier', 'creator'])
            ->when($from_date, function($q) use ($from_date) {
                $q->whereDate('payment_date', '>=', $from_date);
            })
            ->when($to_date, function($q) use ($to_date) {
                $q->whereDate('payment_date', '<=', $to_date);
            })
            ->when($supplier_id, function($q) use ($supplier_id) {
                $q->where('supplier_id', $supplier_id);
            })
            ->orderByDesc('payment_date')
            ->get();
            
        return view('prints.supplier_payments_report', compact('payments', 'from_date', 'to_date'));
    }
} 