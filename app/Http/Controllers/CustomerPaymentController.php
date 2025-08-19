<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('customer')
            ->where('payment_type', 'payment')
            ->orderByDesc('payment_date')
            ->paginate(20);
        
        return view('customer_payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::active()->get();
        return view('customer_payments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $customer = Customer::find($validated['customer_id']);
            
            // التحقق من رصيد العميل
            if ($customer->current_balance < $validated['amount']) {
                throw new \Exception('المبلغ المدفوع أكبر من رصيد العميل');
            }

            // إضافة الدفع لحساب العميل
            $customer->addPayment(
                $validated['amount'],
                $validated['notes'] ?? 'دفع من العميل',
                'customer_payment',
                null
            );

            // إضافة المبلغ للخزنة إذا كان الدفع نقدي
            if ($validated['payment_method'] == 'cash') {
                $treasury = Treasury::first();
                if ($treasury) {
                    $treasury->addReceipt(
                        $validated['amount'],
                        'دفع من العميل: ' . $customer->name,
                        'customer_payment',
                        $customer->id
                    );
                }
            }

            // تسجيل المعاملة في جدول المدفوعات
            Payment::create([
                'customer_id' => $validated['customer_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_type' => 'payment',
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('customer_payments.index')
            ->with('success', 'تم تسجيل الدفع بنجاح');
    }

    public function show(Payment $payment)
    {
        $payment->load('customer');
        return view('customer_payments.show', compact('payment'));
    }

    public function customerPayments($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $payments = Payment::where('customer_id', $customerId)
            ->orderByDesc('payment_date')
            ->paginate(20);
        
        $summary = $customer->getSummary();
        
        return view('customer_payments.customer_payments', compact('customer', 'payments', 'summary'));
    }

    public function customerBalance($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $summary = $customer->getSummary();
        
        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'code' => $customer->code,
            ],
            'balance' => $customer->current_balance,
            'formatted_balance' => number_format($customer->current_balance, 2) . ' دينار ليبي',
            'summary' => $summary,
            'last_transaction' => $customer->last_transaction_date,
        ]);
    }
} 