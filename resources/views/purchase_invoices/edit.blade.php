@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل فاتورة شراء</h4>
    <form action="{{ route('purchase_invoices.update', $purchaseInvoice->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="supplier_id" class="form-label">المورد</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $purchaseInvoice->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="invoice_no" class="form-label">رقم الفاتورة</label>
            <input type="text" name="invoice_no" id="invoice_no" class="form-control" value="{{ $purchaseInvoice->invoice_no }}" required>
        </div>
        <div class="mb-3">
            <label for="invoice_date" class="form-label">تاريخ الفاتورة</label>
            <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ $purchaseInvoice->invoice_date }}" required>
        </div>
        <div class="mb-3">
            <label for="total_amount" class="form-label">الإجمالي</label>
            <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="{{ $purchaseInvoice->total_amount }}" required>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">طريقة الدفع</label>
            <select name="payment_type" id="payment_type" class="form-select" required>
                <option value="cash" {{ old('payment_type', $purchaseInvoice->payment_type ?? 'cash') == 'cash' ? 'selected' : '' }}>نقدًا</option>
                <option value="credit" {{ old('payment_type', $purchaseInvoice->payment_type ?? 'cash') == 'credit' ? 'selected' : '' }}>آجل</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 