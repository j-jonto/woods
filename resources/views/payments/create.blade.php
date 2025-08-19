@extends('layouts.app')

@section('content')
<div class="container">
    <h3>{{ isset($customer) ? 'إضافة إيصال قبض للعميل: ' . $customer->name : 'إضافة إيصال صرف للمورد: ' . $supplier->name }}</h3>
    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        @if(isset($customer))
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        @endif
        @if(isset($supplier))
            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
        @endif
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="payment_date" class="form-label">تاريخ الدفعة</label>
            <input type="date" name="payment_date" id="payment_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="reference" class="form-label">المرجع (اختياري)</label>
            <input type="text" name="reference" id="reference" class="form-control">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ isset($customer) ? route('customers.show', $customer) : route('suppliers.show', $supplier) }}" class="btn btn-secondary">إلغاء</a>
    </form>
    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection 