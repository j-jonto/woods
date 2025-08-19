<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\PurchaseOrder;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsPayableController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with(['purchaseOrders', 'payments'])
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->paginate(20);

        $summary = $this->getSummary();
        
        return view('accounts_payable.index', compact('suppliers', 'summary'));
    }

    public function agingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $suppliers = Supplier::with(['purchaseOrders' => function($query) use ($asOfDate) {
            $query->where('order_date', '<=', $asOfDate)
                  ->where('status', 'received');
        }, 'payments' => function($query) use ($asOfDate) {
            $query->where('payment_date', '<=', $asOfDate);
        }])->get();

        $agingData = [];
        $totalByAge = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
        ];

        foreach ($suppliers as $supplier) {
            $aging = $this->calculateAging($supplier, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'supplier' => $supplier,
                    'aging' => $aging,
                ];

                $totalByAge['current'] += $aging['current'];
                $totalByAge['30_days'] += $aging['30_days'];
                $totalByAge['60_days'] += $aging['60_days'];
                $totalByAge['90_days'] += $aging['90_days'];
                $totalByAge['over_90_days'] += $aging['over_90_days'];
            }
        }

        return view('accounts_payable.aging_report', compact('agingData', 'totalByAge', 'asOfDate'));
    }

    public function supplierStatement($supplierId, Request $request)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $fromDate = $request->get('from_date', now()->subMonths(3));
        $toDate = $request->get('to_date', now());

        $transactions = $this->getSupplierTransactions($supplier, $fromDate, $toDate);
        $summary = $supplier->getSummary();

        return view('accounts_payable.supplier_statement', compact('supplier', 'transactions', 'summary', 'fromDate', 'toDate'));
    }

    public function overdueReport()
    {
        $suppliers = Supplier::with(['purchaseOrders' => function($query) {
            $query->where('status', 'received')
                  ->where('payment_type', 'credit');
        }])->get();

        $overdueData = [];
        $totalOverdue = 0;

        foreach ($suppliers as $supplier) {
            $overdue = $this->calculateOverdue($supplier);
            
            if ($overdue['amount'] > 0) {
                $overdueData[] = [
                    'supplier' => $supplier,
                    'overdue' => $overdue,
                ];
                $totalOverdue += $overdue['amount'];
            }
        }

        return view('accounts_payable.overdue_report', compact('overdueData', 'totalOverdue'));
    }

    public function paymentReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $payments = SupplierPayment::with('supplier')
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date')
            ->get();

        $summary = [
            'total_payments' => $payments->sum('amount'),
            'cash_payments' => $payments->where('payment_method', 'cash')->sum('amount'),
            'bank_payments' => $payments->where('payment_method', 'bank_transfer')->sum('amount'),
            'check_payments' => $payments->where('payment_method', 'check')->sum('amount'),
            'daily_payments' => $payments->groupBy('payment_date')->map(function($day) {
                return $day->sum('amount');
            }),
        ];

        return view('accounts_payable.payment_report', compact('payments', 'summary', 'fromDate', 'toDate'));
    }

    public function pendingPaymentsReport()
    {
        $suppliers = Supplier::with(['purchaseOrders' => function($query) {
            $query->where('status', 'received')
                  ->where('payment_type', 'credit');
        }])->get();

        $pendingData = [];
        $totalPending = 0;

        foreach ($suppliers as $supplier) {
            $pending = $this->calculatePendingPayments($supplier);
            
            if ($pending['amount'] > 0) {
                $pendingData[] = [
                    'supplier' => $supplier,
                    'pending' => $pending,
                ];
                $totalPending += $pending['amount'];
            }
        }

        return view('accounts_payable.pending_payments_report', compact('pendingData', 'totalPending'));
    }

    public function exportAgingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $suppliers = Supplier::with(['purchaseOrders', 'payments'])->get();
        $agingData = [];

        foreach ($suppliers as $supplier) {
            $aging = $this->calculateAging($supplier, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'supplier_code' => $supplier->code,
                    'supplier_name' => $supplier->name,
                    'current' => $aging['current'],
                    '30_days' => $aging['30_days'],
                    '60_days' => $aging['60_days'],
                    '90_days' => $aging['90_days'],
                    'over_90_days' => $aging['over_90_days'],
                    'total' => $aging['total'],
                ];
            }
        }

        return response()->json($agingData);
    }

    private function getSummary()
    {
        $suppliers = Supplier::all();
        
        return [
            'total_suppliers' => $suppliers->count(),
            'active_suppliers' => $suppliers->where('is_active', true)->count(),
            'total_payables' => $suppliers->sum('current_balance'),
            'total_payables_positive' => $suppliers->where('current_balance', '>', 0)->sum('current_balance'),
            'total_payables_negative' => $suppliers->where('current_balance', '<', 0)->sum('current_balance'),
            'suppliers_with_balance' => $suppliers->where('current_balance', '!=', 0)->count(),
        ];
    }

    private function calculateAging($supplier, $asOfDate)
    {
        $purchases = $supplier->purchaseOrders()
            ->where('order_date', '<=', $asOfDate)
            ->where('status', 'received')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $supplier->payments()
            ->where('payment_date', '<=', $asOfDate)
            ->where('status', 'completed')
            ->get();

        $aging = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
            'total' => 0,
        ];

        foreach ($purchases as $purchase) {
            $daysOld = now()->diffInDays($purchase->order_date);
            $amount = $purchase->total_amount;

            if ($daysOld <= 30) {
                $aging['current'] += $amount;
            } elseif ($daysOld <= 60) {
                $aging['30_days'] += $amount;
            } elseif ($daysOld <= 90) {
                $aging['60_days'] += $amount;
            } elseif ($daysOld <= 120) {
                $aging['90_days'] += $amount;
            } else {
                $aging['over_90_days'] += $amount;
            }
        }

        // خصم المدفوعات
        $totalPayments = $payments->sum('amount');
        $aging['total'] = array_sum($aging) - $totalPayments;

        return $aging;
    }

    private function calculateOverdue($supplier)
    {
        $purchases = $supplier->purchaseOrders()
            ->where('status', 'received')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $supplier->payments()
            ->where('status', 'completed')
            ->get();

        $totalPurchases = $purchases->sum('total_amount');
        $totalPayments = $payments->sum('amount');
        $balance = $totalPurchases - $totalPayments;

        $overdueAmount = 0;
        $overdueDays = 0;

        if ($balance > 0) {
            $oldestPurchase = $purchases->sortBy('order_date')->first();
            if ($oldestPurchase) {
                $overdueDays = now()->diffInDays($oldestPurchase->order_date);
                if ($overdueDays > 30) {
                    $overdueAmount = $balance;
                }
            }
        }

        return [
            'amount' => $overdueAmount,
            'days' => $overdueDays,
            'total_balance' => $balance,
        ];
    }

    private function calculatePendingPayments($supplier)
    {
        $purchases = $supplier->purchaseOrders()
            ->where('status', 'received')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $supplier->payments()
            ->where('status', 'completed')
            ->get();

        $totalPurchases = $purchases->sum('total_amount');
        $totalPayments = $payments->sum('amount');
        $pendingAmount = $totalPurchases - $totalPayments;

        return [
            'amount' => max(0, $pendingAmount),
            'total_purchases' => $totalPurchases,
            'total_payments' => $totalPayments,
        ];
    }

    private function getSupplierTransactions($supplier, $fromDate, $toDate)
    {
        $transactions = collect();

        // إضافة المشتريات
        $purchases = $supplier->purchaseOrders()
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->where('status', 'received')
            ->get();

        foreach ($purchases as $purchase) {
            $transactions->push([
                'date' => $purchase->order_date,
                'type' => 'purchase',
                'reference' => $purchase->order_no,
                'description' => 'مشتريات',
                'debit' => 0,
                'credit' => $purchase->total_amount,
                'balance' => 0,
            ]);
        }

        // إضافة المدفوعات
        $payments = $supplier->payments()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->where('status', 'completed')
            ->get();

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'reference' => $payment->reference_no,
                'description' => 'دفع',
                'debit' => $payment->amount,
                'credit' => 0,
                'balance' => 0,
            ]);
        }

        // ترتيب حسب التاريخ وحساب الرصيد
        $transactions = $transactions->sortBy('date');
        $balance = 0;

        foreach ($transactions as $transaction) {
            $balance += $transaction['credit'] - $transaction['debit'];
            $transaction['balance'] = $balance;
        }

        return $transactions;
    }
} 