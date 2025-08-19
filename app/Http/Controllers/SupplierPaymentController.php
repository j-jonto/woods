<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function index()
    {
        $payments = SupplierPayment::with('supplier')
            ->orderByDesc('payment_date')
            ->paginate(20);
        
        return view('supplier_payments.index', compact('payments'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->get();
        return view('supplier_payments.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'reference_no' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $supplier = Supplier::find($validated['supplier_id']);
            
            // التحقق من رصيد المورد
            if ($supplier->current_balance < $validated['amount']) {
                throw new \Exception('المبلغ المدفوع أكبر من رصيد المورد');
            }

            // إضافة الدفع لحساب المورد
            $supplier->addPayment(
                $validated['amount'],
                $validated['notes'] ?? 'دفع للمورد',
                'supplier_payment',
                null
            );

            // خصم المبلغ من الخزنة إذا كان الدفع نقدي
            if ($validated['payment_method'] == 'cash') {
                $treasury = Treasury::first();
                if ($treasury) {
                    $treasury->addPayment(
                        $validated['amount'],
                        'دفع للمورد: ' . $supplier->name,
                        'supplier_payment',
                        $supplier->id
                    );
                }
            }

            // تسجيل المعاملة في جدول مدفوعات الموردين
            SupplierPayment::create([
                'supplier_id' => $validated['supplier_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_no' => $validated['reference_no'],
                'notes' => $validated['notes'],
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('supplier_payments.index')
            ->with('success', 'تم تسجيل الدفع بنجاح');
    }

    public function show(SupplierPayment $payment)
    {
        $payment->load('supplier');
        return view('supplier_payments.show', compact('payment'));
    }

    public function supplierPayments($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $payments = SupplierPayment::where('supplier_id', $supplierId)
            ->orderByDesc('payment_date')
            ->paginate(20);
        
        $summary = $supplier->getSummary();
        
        return view('supplier_payments.supplier_payments', compact('supplier', 'payments', 'summary'));
    }

    public function supplierBalance($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $summary = $supplier->getSummary();
        
        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'code' => $supplier->code,
            ],
            'balance' => $supplier->current_balance,
            'formatted_balance' => number_format($supplier->current_balance, 2) . ' دينار ليبي',
            'summary' => $summary,
            'last_transaction' => $supplier->last_transaction_date,
        ]);
    }

    public function pendingPayments()
    {
        $payments = SupplierPayment::with('supplier')
            ->where('status', 'pending')
            ->orderBy('payment_date')
            ->paginate(20);
        
        return view('supplier_payments.pending', compact('payments'));
    }

    public function markAsPaid($paymentId)
    {
        $payment = SupplierPayment::findOrFail($paymentId);
        
        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'completed']);
            
            // خصم المبلغ من الخزنة
            $treasury = Treasury::first();
            if ($treasury) {
                $treasury->addPayment(
                    $payment->amount,
                    'دفع للمورد: ' . $payment->supplier->name,
                    'supplier_payment',
                    $payment->supplier_id
                );
            }
        });

        return redirect()->back()->with('success', 'تم تحديث حالة الدفع بنجاح');
    }
} 