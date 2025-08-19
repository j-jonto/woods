@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل فاتورة الشراء</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>المورد:</strong> {{ $purchaseInvoice->supplier ? $purchaseInvoice->supplier->name : '-' }}</p>
            <p><strong>رقم الفاتورة:</strong> {{ $purchaseInvoice->invoice_no }}</p>
            <p><strong>تاريخ الفاتورة:</strong> {{ $purchaseInvoice->invoice_date }}</p>
            <p><strong>الإجمالي:</strong> {{ number_format($purchaseInvoice->total_amount, 2) }}</p>
            <p><strong>الحالة:</strong> {{ $purchaseInvoice->status }}</p>
            <p><strong>طريقة الدفع:</strong> {{ $purchaseInvoice->payment_type == 'cash' ? 'نقدًا' : 'آجل' }}</p>
        </div>
    </div>
    <a href="{{ route('purchase_invoices.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('print.purchase_invoice', $purchaseInvoice->id) }}" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> طباعة الفاتورة
    </a>
</div>
@endsection 