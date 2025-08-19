@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل سند القبض</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الحساب:</strong> {{ $receiptVoucher->account ? $receiptVoucher->account->name : '-' }}</p>
            <p><strong>المبلغ:</strong> {{ number_format($receiptVoucher->amount, 2) }}</p>
            <p><strong>التاريخ:</strong> {{ $receiptVoucher->date }}</p>
            <p><strong>المصدر:</strong> {{ $receiptVoucher->source }}</p>
        </div>
    </div>
    <a href="{{ route('receipt_vouchers.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('print.receipt_voucher', $receiptVoucher->id) }}" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> طباعة الإيصال
    </a>
</div>
@endsection 