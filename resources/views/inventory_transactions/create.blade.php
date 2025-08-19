@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تسجيل معاملة مخزون جديدة</h1>

    <form action="{{ route('inventory_transactions.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="transaction_date" class="form-label">تاريخ المعاملة</label>
            <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label for="reference_no" class="form-label">رقم المرجع</label>
            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ old('reference_no') }}" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">نوع المعاملة</label>
            <select class="form-select" id="type" name="type" required>
                <option value="">اختر النوع</option>
                <option value="receipt" {{ old('type') == 'receipt' ? 'selected' : '' }}>إدخال مخزوني</option>
                <option value="issue" {{ old('type') == 'issue' ? 'selected' : '' }}>صرف مواد</option>
                <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>تحويل مخزون</option>
                <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>تسوية مخزون</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="item_id" class="form-label">الصنف</label>
            <select class="form-select" id="item_id" name="item_id" required>
                <option value="">اختر الصنف</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} ({{ $item->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="warehouse_id" class="form-label">المخزن</label>
            <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                <option value="">اختر المخزن</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">الكمية</label>
            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
        </div>
        <div class="mb-3">
            <label for="unit_cost" class="form-label">تكلفة الوحدة (للإدخال/التسوية)</label>
            <input type="number" step="0.01" class="form-control" id="unit_cost" name="unit_cost" value="{{ old('unit_cost', 0) }}">
        </div>
        <div class="mb-3">
            <label for="batch_no" class="form-label">رقم الدفعة/اللوط (اختياري)</label>
            <input type="text" class="form-control" id="batch_no" name="batch_no" value="{{ old('batch_no') }}">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف / السبب</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تسجيل المعاملة</button>
        <a href="{{ route('inventory_transactions.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 