@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل سند الصرف</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الحساب:</strong> {{ $paymentVoucher->account ? $paymentVoucher->account->name : '-' }}</p>
            <p><strong>المبلغ:</strong> {{ number_format($paymentVoucher->amount, 2) }}</p>
            <p><strong>التاريخ:</strong> {{ $paymentVoucher->date }}</p>
            <p><strong>الجهة المستفيدة:</strong> {{ $paymentVoucher->destination }}</p>
        </div>
    </div>
    <a href="{{ route('payment_vouchers.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('print.payment_voucher', $paymentVoucher->id) }}" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> طباعة السند
    </a>
</div>
@endsection