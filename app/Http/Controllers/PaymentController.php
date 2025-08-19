<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createForCustomer(Customer $customer)
    {
        return view('payments.create', [
            'customer' => $customer,
            'type' => 'receipt',
        ]);
    }

    public function createForSupplier(Supplier $supplier)
    {
        return view('payments.create', [
            'supplier' => $supplier,
            'type' => 'disbursement',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,disbursement',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $validated['created_by'] = $request->user()->id ?? null;
        $payment = Payment::create($validated);
        if ($validated['type'] === 'receipt') {
            return redirect()->route('payments.show', $payment->id)
                ->with('success', 'تم إضافة إيصال القبض بنجاح');
        } else {
            return redirect()->route('payments.show', $payment->id)
                ->with('success', 'تم إضافة إيصال الصرف بنجاح');
        }
    }

    public function show(Payment $payment)
    {
        $payment->load(['customer', 'supplier', 'creator']);
        return view('payments.show', compact('payment'));
    }
} 