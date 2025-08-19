@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة فاتورة شراء جديدة</h4>
    <form action="{{ route('purchase_invoices.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="supplier_id" class="form-label">المورد</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                <option value="">اختر المورد</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="invoice_no" class="form-label">رقم الفاتورة</label>
            <input type="text" name="invoice_no" id="invoice_no" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="invoice_date" class="form-label">تاريخ الفاتورة</label>
            <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="total_amount" class="form-label">الإجمالي</label>
            <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">طريقة الدفع</label>
            <select name="payment_type" id="payment_type" class="form-select" required>
                <option value="cash" {{ old('payment_type', 'cash') == 'cash' ? 'selected' : '' }}>نقدًا</option>
                <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 