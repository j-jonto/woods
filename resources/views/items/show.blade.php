@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h1>تفاصيل المنتج: {{ $item->name }}</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('items.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>خطأ:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات المنتج</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الرمز:</strong></div>
                        <div class="col-sm-8">{{ $item->code }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الاسم:</strong></div>
                        <div class="col-sm-8">{{ $item->name }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>النوع:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الفئة:</strong></div>
                        <div class="col-sm-8">{{ $item->category->name ?? 'غير محدد' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>وحدة القياس:</strong></div>
                        <div class="col-sm-8">{{ $item->unit_of_measure }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الباركود:</strong></div>
                        <div class="col-sm-8">{{ $item->barcode ?? 'غير محدد' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الوصف:</strong></div>
                        <div class="col-sm-8">{{ $item->description ?? 'لا يوجد وصف' }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>الحالة:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-{{ $item->is_active ? 'success' : 'danger' }}">
                                {{ $item->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات المخزون</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>المخزون المتاح:</strong></div>
                        <div class="col-sm-6">
                            @if($item->available_stock > 0)
                                <span class="badge bg-success fs-6">{{ number_format($item->available_stock, 2) }} {{ $item->unit_of_measure }}</span>
                            @elseif($item->available_stock == 0)
                                <span class="badge bg-danger fs-6">نفذ المخزون</span>
                            @else
                                <span class="badge bg-warning fs-6">{{ number_format($item->available_stock, 2) }} {{ $item->unit_of_measure }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>إجمالي الاستلام:</strong></div>
                        <div class="col-sm-6">{{ number_format($item->total_receipts, 2) }} {{ $item->unit_of_measure }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>إجمالي الصرف:</strong></div>
                        <div class="col-sm-6">{{ number_format($item->total_issues, 2) }} {{ $item->unit_of_measure }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>نقطة إعادة الطلب:</strong></div>
                        <div class="col-sm-6">{{ number_format($item->reorder_point, 2) }} {{ $item->unit_of_measure }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>كمية إعادة الطلب:</strong></div>
                        <div class="col-sm-6">{{ number_format($item->reorder_quantity, 2) }} {{ $item->unit_of_measure }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>حالة المخزون:</strong></div>
                        <div class="col-sm-6">
                            @if($item->is_out_of_stock)
                                <span class="badge bg-danger">نفذ المخزون</span>
                            @elseif($item->is_low_stock)
                                <span class="badge bg-warning">مخزون منخفض</span>
                            @else
                                <span class="badge bg-success">مخزون كافي</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">معلومات الأسعار</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>التكلفة القياسية:</strong></div>
                        <div class="col-sm-6">${{ number_format($item->standard_cost, 2) }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>سعر البيع:</strong></div>
                        <div class="col-sm-6">${{ number_format($item->selling_price, 2) }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>هامش الربح:</strong></div>
                        <div class="col-sm-6">${{ number_format($item->profit_per_unit, 2) }}</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6"><strong>نسبة الربح:</strong></div>
                        <div class="col-sm-6">{{ number_format($item->profit_margin, 2) }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">آخر حركات المخزون</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>رقم المرجع</th>
                            <th>النوع</th>
                            <th>الكمية</th>
                            <th>المستودع</th>
                            <th>الوصف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->inventoryTransactions()->orderByDesc('transaction_date')->take(10)->get() as $transaction)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                                <td>{{ $transaction->reference_no }}</td>
                                <td>
                                    @if($transaction->type == 'receipt')
                                        <span class="badge bg-success">استلام</span>
                                    @elseif($transaction->type == 'issue')
                                        <span class="badge bg-danger">صرف</span>
                                    @elseif($transaction->type == 'transfer')
                                        <span class="badge bg-info">نقل</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $transaction->type }}</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($transaction->quantity, 2) }}</td>
                                <td>{{ $transaction->warehouse->name ?? 'غير محدد' }}</td>
                                <td>{{ $transaction->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">لا توجد حركات مخزون لهذا المنتج</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">الإجراءات</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل المنتج
            </a>
            <a href="{{ route('inventory_transactions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة معاملة مخزون
            </a>
            <a href="{{ route('inventory_transactions.index') }}" class="btn btn-info">
                <i class="fas fa-list"></i> عرض جميع معاملات المخزون
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // إخفاء رسائل النجاح بعد 5 ثواني
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
});
</script>
@endpush
@endsection 