@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>إيصال {{ $payment->type == 'receipt' ? 'قبض' : 'صرف' }}</h3>
        <div>
            <button class="btn btn-primary" onclick="window.print();return false;">
                <i class="fas fa-print"></i> طباعة الإيصال
            </button>
            <a href="{{ $payment->customer ? route('customers.show', $payment->customer) : route('suppliers.show', $payment->supplier) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="print-area border p-4 bg-white shadow-sm">
        <div class="text-center mb-4">
            <h4 class="text-primary">إيصال {{ $payment->type == 'receipt' ? 'قبض' : 'صرف' }}</h4>
            <p class="text-muted">رقم الإيصال: {{ $payment->id }}</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="fw-bold">المبلغ:</label>
                    <div class="h5 text-success">{{ number_format($payment->amount, 2) }} دينار ليبي</div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">التاريخ:</label>
                    <div>{{ $payment->payment_date }}</div>
                </div>
                @if($payment->customer)
                    <div class="mb-3">
                        <label class="fw-bold">العميل:</label>
                        <div>{{ $payment->customer->name }}</div>
                    </div>
                @endif
                @if($payment->supplier)
                    <div class="mb-3">
                        <label class="fw-bold">المورد:</label>
                        <div>{{ $payment->supplier->name }}</div>
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="fw-bold">المرجع:</label>
                    <div>{{ $payment->reference ?? '---' }}</div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">ملاحظات:</label>
                    <div>{{ $payment->notes ?? '---' }}</div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">تم بواسطة:</label>
                    <div>{{ $payment->creator?->name ?? '---' }}</div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">تاريخ الإنشاء:</label>
                    <div>{{ $payment->created_at->format('Y-m-d H:i') }}</div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top">
            <small class="text-muted">هذا الإيصال صالح للطباعة والاستخدام الرسمي</small>
        </div>
    </div>
</div>
<style>
@media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100%; 
        border: none !important;
        box-shadow: none !important;
    }
    .btn, nav, header, footer, .alert { display: none !important; }
    .text-primary { color: #000 !important; }
    .text-success { color: #000 !important; }
    .text-muted { color: #666 !important; }
    .shadow-sm { box-shadow: none !important; }
}
</style>
@endsection 