<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsReceivableController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['salesOrders', 'payments'])
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->paginate(20);

        $summary = $this->getSummary();
        
        return view('accounts_receivable.index', compact('customers', 'summary'));
    }

    public function agingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $customers = Customer::with(['salesOrders' => function($query) use ($asOfDate) {
            $query->where('order_date', '<=', $asOfDate)
                  ->where('status', 'invoiced');
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

        foreach ($customers as $customer) {
            $aging = $this->calculateAging($customer, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'customer' => $customer,
                    'aging' => $aging,
                ];

                $totalByAge['current'] += $aging['current'];
                $totalByAge['30_days'] += $aging['30_days'];
                $totalByAge['60_days'] += $aging['60_days'];
                $totalByAge['90_days'] += $aging['90_days'];
                $totalByAge['over_90_days'] += $aging['over_90_days'];
            }
        }

        return view('accounts_receivable.aging_report', compact('agingData', 'totalByAge', 'asOfDate'));
    }

    public function customerStatement($customerId, Request $request)
    {
        $customer = Customer::findOrFail($customerId);
        $fromDate = $request->get('from_date', now()->subMonths(3));
        $toDate = $request->get('to_date', now());

        $transactions = $this->getCustomerTransactions($customer, $fromDate, $toDate);
        $summary = $customer->getSummary();

        return view('accounts_receivable.customer_statement', compact('customer', 'transactions', 'summary', 'fromDate', 'toDate'));
    }

    public function overdueReport()
    {
        $customers = Customer::with(['salesOrders' => function($query) {
            $query->where('status', 'invoiced')
                  ->where('payment_type', 'credit');
        }])->get();

        $overdueData = [];
        $totalOverdue = 0;

        foreach ($customers as $customer) {
            $overdue = $this->calculateOverdue($customer);
            
            if ($overdue['amount'] > 0) {
                $overdueData[] = [
                    'customer' => $customer,
                    'overdue' => $overdue,
                ];
                $totalOverdue += $overdue['amount'];
            }
        }

        return view('accounts_receivable.overdue_report', compact('overdueData', 'totalOverdue'));
    }

    public function collectionReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $collections = Payment::with('customer')
            ->where('payment_type', 'payment')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date')
            ->get();

        $summary = [
            'total_collections' => $collections->sum('amount'),
            'cash_collections' => $collections->where('payment_method', 'cash')->sum('amount'),
            'bank_collections' => $collections->where('payment_method', 'bank_transfer')->sum('amount'),
            'check_collections' => $collections->where('payment_method', 'check')->sum('amount'),
            'daily_collections' => $collections->groupBy('payment_date')->map(function($day) {
                return $day->sum('amount');
            }),
        ];

        return view('accounts_receivable.collection_report', compact('collections', 'summary', 'fromDate', 'toDate'));
    }

    public function exportAgingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $customers = Customer::with(['salesOrders', 'payments'])->get();
        $agingData = [];

        foreach ($customers as $customer) {
            $aging = $this->calculateAging($customer, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'customer_code' => $customer->code,
                    'customer_name' => $customer->name,
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
        $customers = Customer::all();
        
        return [
            'total_customers' => $customers->count(),
            'active_customers' => $customers->where('is_active', true)->count(),
            'total_receivables' => $customers->sum('current_balance'),
            'total_receivables_positive' => $customers->where('current_balance', '>', 0)->sum('current_balance'),
            'total_receivables_negative' => $customers->where('current_balance', '<', 0)->sum('current_balance'),
            'customers_with_balance' => $customers->where('current_balance', '!=', 0)->count(),
            'customers_over_limit' => $customers->where('current_balance', '>', DB::raw('credit_limit'))->count(),
        ];
    }

    private function calculateAging($customer, $asOfDate)
    {
        $sales = $customer->salesOrders()
            ->where('order_date', '<=', $asOfDate)
            ->where('status', 'invoiced')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $customer->payments()
            ->where('payment_date', '<=', $asOfDate)
            ->where('payment_type', 'payment')
            ->get();

        $aging = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
            'total' => 0,
        ];

        foreach ($sales as $sale) {
            $daysOld = now()->diffInDays($sale->order_date);
            $amount = $sale->total_amount;

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

    private function calculateOverdue($customer)
    {
        $sales = $customer->salesOrders()
            ->where('status', 'invoiced')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $customer->payments()
            ->where('payment_type', 'payment')
            ->get();

        $totalSales = $sales->sum('total_amount');
        $totalPayments = $payments->sum('amount');
        $balance = $totalSales - $totalPayments;

        $overdueAmount = 0;
        $overdueDays = 0;

        if ($balance > 0) {
            $oldestSale = $sales->sortBy('order_date')->first();
            if ($oldestSale) {
                $overdueDays = now()->diffInDays($oldestSale->order_date);
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

    private function getCustomerTransactions($customer, $fromDate, $toDate)
    {
        $transactions = collect();

        // إضافة المبيعات
        $sales = $customer->salesOrders()
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->where('status', 'invoiced')
            ->get();

        foreach ($sales as $sale) {
            $transactions->push([
                'date' => $sale->order_date,
                'type' => 'sale',
                'reference' => $sale->order_no,
                'description' => 'مبيعات',
                'debit' => $sale->total_amount,
                'credit' => 0,
                'balance' => 0,
            ]);
        }

        // إضافة المدفوعات
        $payments = $customer->payments()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->where('payment_type', 'payment')
            ->get();

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'payment',
                'reference' => $payment->reference_no,
                'description' => 'دفع',
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0,
            ]);
        }

        // ترتيب حسب التاريخ وحساب الرصيد
        $transactions = $transactions->sortBy('date');
        $balance = 0;

        foreach ($transactions as $transaction) {
            $balance += $transaction['debit'] - $transaction['credit'];
            $transaction['balance'] = $balance;
        }

        return $transactions;
    }
} 