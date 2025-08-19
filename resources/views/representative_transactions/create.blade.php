@extends('layouts.app')

@section('content')
<div class="container">
    <h3>إضافة حركة للمندوب: {{ $salesRepresentative->name }}</h3>
    <form method="POST" action="{{ route('representative_transactions.store') }}">
        @csrf
        <input type="hidden" name="representative_id" value="{{ $salesRepresentative->id }}">
        
        <div class="mb-3">
            <label for="type" class="form-label">نوع الحركة</label>
            <select name="type" id="type" class="form-select" required>
                <option value="goods_received" {{ old('type') == 'goods_received' ? 'selected' : '' }}>بضاعة مستلمة</option>
                <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>دفعة للشركة</option>
                <option value="commission" {{ old('type') == 'commission' ? 'selected' : '' }}>عمولة إضافية</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
        </div>
        
        <div class="mb-3">
            <label for="transaction_date" class="form-label">تاريخ الحركة</label>
            <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
        </div>
        
        <div class="mb-3">
            <label for="reference" class="form-label">المرجع (اختياري)</label>
            <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}">
        </div>
        
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" class="form-control">{{ old('notes') }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('sales_representatives.show', $salesRepresentative) }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 