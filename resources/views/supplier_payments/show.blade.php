@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل دفعة المورد</h4>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>المورد:</strong> {{ $supplierPayment->supplier ? $supplierPayment->supplier->name : '-' }}</p>
                    <p><strong>المبلغ:</strong> {{ number_format($supplierPayment->amount, 2) }} دينار ليبي</p>
                    <p><strong>تاريخ الدفع:</strong> {{ $supplierPayment->payment_date }}</p>
                    <p><strong>طريقة الدفع:</strong> {{ $supplierPayment->method }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>الحالة:</strong> 
                        @if($supplierPayment->status == 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @else
                            <span class="badge bg-success">مدفوع</span>
                        @endif
                    </p>
                    @if($supplierPayment->reference_type == 'purchase_order')
                        <p><strong>مرتبط بـ:</strong> أمر شراء</p>
                    @endif
                    <p><strong>تاريخ الإنشاء:</strong> {{ $supplierPayment->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">الإجراءات</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="{{ route('supplier_payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> رجوع
                </a>
                
                @if($supplierPayment->status == 'pending')
                    <form action="{{ route('supplier_payments.pay', $supplierPayment->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('هل أنت متأكد من دفع هذا المبلغ؟ سيتم خصمه من الخزنة العامة.')">
                            <i class="fas fa-money-bill"></i> دفع المبلغ
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('print.supplier_payments_report') }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-print"></i> طباعة تقرير
                </a>
            </div>
            
            @if($supplierPayment->status == 'pending')
                <div class="alert alert-warning mt-3">
                    <small><i class="fas fa-info-circle"></i> هذه الدفعة قيد الانتظار. عند الدفع سيتم خصم المبلغ من الخزنة العامة.</small>
                </div>
            @else
                <div class="alert alert-success mt-3">
                    <small><i class="fas fa-info-circle"></i> تم دفع هذا المبلغ وخصمه من الخزنة العامة.</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 