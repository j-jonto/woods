@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>أوامر الإنتاج</h1>
        <a href="{{ route('production_orders.create') }}" class="btn btn-primary">إنشاء أمر إنتاج جديد</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($productionOrders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>رقم الأمر</th>
                                <th>المنتج</th>
                                <th>قائمة المواد</th>
                                <th>مركز العمل</th>
                                <th>الكمية</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productionOrders as $order)
                                <tr>
                                    <td>{{ $order->order_no }}</td>
                                    <td>
                                        <strong>{{ $order->item ? $order->item->name : 'غير متوفر' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->item ? $order->item->code : '-' }}</small>
                                    </td>
                                    <td>
                                        @if($order->billOfMaterial)
                                            <strong>{{ $order->billOfMaterial->finishedGood ? $order->billOfMaterial->finishedGood->name : 'غير محدد' }}</strong>
                                            <br>
                                            <small class="text-muted">يحتاج: {{ $order->billOfMaterial->quantity }} {{ $order->billOfMaterial->rawMaterial ? $order->billOfMaterial->rawMaterial->unit_of_measure : 'وحدة' }}</small>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $order->workCenter ? $order->workCenter->name : 'غير محدد' }}
                                    </td>
                                    <td>{{ number_format($order->quantity, 2) }} {{ $order->item ? $order->item->unit_of_measure : '' }}</td>
                                    <td>{{ $order->start_date ? \Carbon\Carbon::parse($order->start_date)->format('Y-m-d') : 'غير محدد' }}</td>
                                    <td>{{ $order->end_date ? \Carbon\Carbon::parse($order->end_date)->format('Y-m-d') : 'غير محدد' }}</td>
                                    <td>
                                        @if($order->status == 'draft')
                                            <span class="badge bg-secondary">مسودة</span>
                                        @elseif($order->status == 'released')
                                            <span class="badge bg-warning">مفعل</span>
                                        @elseif($order->status == 'in_progress')
                                            <span class="badge bg-info">قيد التنفيذ</span>
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @else
                                            <span class="badge bg-danger">ملغي</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('production_orders.show', $order->id) }}" 
                                               class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($order->status == 'draft' || $order->status == 'released')
                                                <a href="{{ route('production_orders.edit', $order->id) }}" 
                                                   class="btn btn-sm btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('production_orders.destroy', $order->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="حذف" 
                                                            onclick="return confirm('هل أنت متأكد من حذف أمر الإنتاج: {{ $order->order_no }}؟')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if ($order->status == 'draft')
                                                <form action="{{ route('production_orders.activate', $order->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-primary" 
                                                            title="تفعيل الأمر" 
                                                            onclick="return confirm('هل أنت متأكد من تفعيل أمر الإنتاج: {{ $order->order_no }}؟')">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if ($order->status == 'released')
                                                <form action="{{ route('production_orders.start', $order->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="بدء الإنتاج" 
                                                            onclick="return confirm('هل أنت متأكد من بدء الإنتاج: {{ $order->order_no }}؟')">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $productionOrders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-industry fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد أوامر إنتاج</h5>
                    <p class="text-muted">ابدأ بإنشاء أمر إنتاج جديد</p>
                    <a href="{{ route('production_orders.create') }}" class="btn btn-primary">إنشاء أمر إنتاج</a>
                </div>
            @endif
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