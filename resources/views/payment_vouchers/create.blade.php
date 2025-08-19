@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة سند صرف جديد</h4>
    <form action="{{ route('payment_vouchers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="account_id" class="form-label">الحساب</label>
            <select name="account_id" id="account_id" class="form-select" required>
                <option value="">اختر الحساب</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="destination" class="form-label">الجهة المستفيدة</label>
            <input type="text" name="destination" id="destination" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('payment_vouchers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection