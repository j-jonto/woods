@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة دفعة مورد جديدة</h4>
    <form action="{{ route('supplier_payments.store') }}" method="POST">
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
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="payment_date" class="form-label">تاريخ الدفع</label>
            <input type="date" name="payment_date" id="payment_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="method" class="form-label">طريقة الدفع</label>
            <input type="text" name="method" id="method" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('supplier_payments.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 