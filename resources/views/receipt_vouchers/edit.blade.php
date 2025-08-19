@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل سند قبض</h4>
    <form action="{{ route('receipt_vouchers.update', $receiptVoucher->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="account_id" class="form-label">الحساب</label>
            <select name="account_id" id="account_id" class="form-select" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ $receiptVoucher->account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $receiptVoucher->amount }}" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $receiptVoucher->date }}" required>
        </div>
        <div class="mb-3">
            <label for="source" class="form-label">المصدر</label>
            <input type="text" name="source" id="source" class="form-control" value="{{ $receiptVoucher->source }}">
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('receipt_vouchers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 