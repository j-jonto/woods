<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function index()
    {
        $invoices = PurchaseInvoice::with('supplier')->orderByDesc('invoice_date')->paginate(20);
        return view('purchase_invoices.index', compact('invoices'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        return view('purchase_invoices.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no' => 'required|unique:purchase_invoices',
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'payment_type' => 'required|in:cash,credit',
        ]);
        $invoice = PurchaseInvoice::create($validated + [
            'status' => 'draft',
            'created_by' => $request->user()->id ?? null,
        ]);
        // حفظ الأصناف (اختياري)
        // ...
        return redirect()->route('purchase_invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح');
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load('supplier', 'items');
        return view('purchase_invoices.show', compact('purchaseInvoice'));
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        $suppliers = Supplier::all();
        return view('purchase_invoices.edit', compact('purchaseInvoice', 'suppliers'));
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no' => 'required|unique:purchase_invoices,invoice_no,' . $purchaseInvoice->id,
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'payment_type' => 'required|in:cash,credit',
        ]);
        $purchaseInvoice->update($validated);
        // تحديث الأصناف (اختياري)
        // ...
        return redirect()->route('purchase_invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح');
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->delete();
        return redirect()->route('purchase_invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }
} 