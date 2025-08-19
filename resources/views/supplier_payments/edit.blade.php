@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل دفعة مورد</h4>
    <form action="{{ route('supplier_payments.update', $supplierPayment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="supplier_id" class="form-label">المورد</label>
            <select name="supplier_id" id="supplier_id" class="form-select" required>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $supplierPayment->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $supplierPayment->amount }}" required>
        </div>
        <div class="mb-3">
            <label for="payment_date" class="form-label">تاريخ الدفع</label>
            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ $supplierPayment->payment_date }}" required>
        </div>
        <div class="mb-3">
            <label for="method" class="form-label">طريقة الدفع</label>
            <input type="text" name="method" id="method" class="form-control" value="{{ $supplierPayment->method }}">
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('supplier_payments.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 