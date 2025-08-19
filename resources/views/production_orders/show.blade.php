@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h1>تفاصيل أمر الإنتاج: {{ $productionOrder->order_no }}</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('production_orders.index') }}" class="btn btn-secondary">العودة للقائمة</a>
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

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">معلومات الأمر</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>رقم الأمر:</h6>
                    <p class="text-primary">{{ $productionOrder->order_no }}</p>
                    
                    <h6>المنتج:</h6>
                    <p class="text-success">
                        {{ $productionOrder->item ? $productionOrder->item->name : 'غير محدد' }}
                        @if($productionOrder->item)
                            <br><small class="text-muted">({{ $productionOrder->item->code }})</small>
                        @endif
                    </p>
                    
                    <h6>الكمية:</h6>
                    <p>{{ number_format($productionOrder->quantity, 2) }} 
                       {{ $productionOrder->item ? $productionOrder->item->unit_of_measure : 'وحدة' }}</p>
                    
                    <h6>تاريخ البدء:</h6>
                    <p>{{ $productionOrder->start_date ? \Carbon\Carbon::parse($productionOrder->start_date)->format('Y-m-d') : 'غير محدد' }}</p>
                </div>
                <div class="col-md-6">
                    <h6>تاريخ الانتهاء:</h6>
                    <p>{{ $productionOrder->end_date ? \Carbon\Carbon::parse($productionOrder->end_date)->format('Y-m-d') : 'غير محدد' }}</p>
                    
                    <h6>مركز العمل:</h6>
                    <p>{{ $productionOrder->workCenter ? $productionOrder->workCenter->name : 'غير محدد' }}</p>
                    
                    <h6>الحالة:</h6>
                    @if($productionOrder->status == 'draft')
                        <span class="badge bg-secondary">مسودة</span>
                        <div class="alert alert-info mt-2">
                            <small><i class="fas fa-info-circle"></i> أمر الإنتاج في حالة مسودة. يجب تفعيله أولاً قبل بدء الإنتاج.</small>
                        </div>
                    @elseif($productionOrder->status == 'released')
                        <span class="badge bg-warning">مفعل</span>
                        <div class="alert alert-warning mt-2">
                            <small><i class="fas fa-info-circle"></i> أمر الإنتاج مفعل ومستعد للبدء. عند بدء الإنتاج سيتم خصم المواد الخام من المخزون.</small>
                        </div>
                    @elseif($productionOrder->status == 'in_progress')
                        <span class="badge bg-info">قيد التنفيذ</span>
                        <div class="alert alert-info mt-2">
                            <small><i class="fas fa-info-circle"></i> الإنتاج قيد التنفيذ. تم خصم المواد الخام من المخزون.</small>
                        </div>
                    @elseif($productionOrder->status == 'completed')
                        <span class="badge bg-success">مكتمل</span>
                        <div class="alert alert-success mt-2">
                            <small><i class="fas fa-info-circle"></i> تم إكمال الإنتاج وإضافة المنتج النهائي إلى المخزون.</small>
                        </div>
                    @else
                        <span class="badge bg-danger">ملغي</span>
                    @endif
                    
                    <h6>الملاحظات:</h6>
                    <p>{{ $productionOrder->notes ?? 'لا توجد ملاحظات' }}</p>
                    
                    <h6>تاريخ الإنشاء:</h6>
                    <p>{{ $productionOrder->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($productionOrder->billOfMaterial)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">قائمة المواد المطلوبة</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>المنتج النهائي:</h6>
                    <p>{{ $productionOrder->billOfMaterial->finishedGood ? $productionOrder->billOfMaterial->finishedGood->name : 'غير محدد' }}</p>
                </div>
                <div class="col-md-6">
                    <h6>المواد الخام المطلوبة:</h6>
                    <p>{{ $productionOrder->billOfMaterial->rawMaterial ? $productionOrder->billOfMaterial->rawMaterial->name : 'غير محدد' }}</p>
                    <p class="text-muted">
                        الكمية المطلوبة: {{ $productionOrder->billOfMaterial->quantity * $productionOrder->quantity }} 
                        {{ $productionOrder->billOfMaterial->rawMaterial ? $productionOrder->billOfMaterial->rawMaterial->unit_of_measure : 'وحدة' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($productionOrder->materials && $productionOrder->materials->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">المواد المستخدمة في الإنتاج</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>المادة</th>
                            <th class="text-end">الكمية المخططة</th>
                            <th class="text-end">الكمية الفعلية</th>
                            <th class="text-end">التكلفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productionOrder->materials as $material)
                            <tr>
                                <td>
                                    {{ $material->material ? $material->material->name : 'غير محدد' }}
                                    @if($material->material)
                                        <br><small class="text-muted">({{ $material->material->code }})</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($material->planned_quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($material->actual_quantity ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($material->total_cost, 2) }} دينار</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">الإجراءات</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                @if ($productionOrder->status == 'draft' || $productionOrder->status == 'released')
                    <a href="{{ route('production_orders.edit', $productionOrder->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> تعديل الأمر
                    </a>
                @endif
                
                @if ($productionOrder->status == 'draft')
                    <form action="{{ route('production_orders.activate', $productionOrder->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-primary" 
                                onclick="return confirm('هل أنت متأكد من تفعيل أمر الإنتاج؟')">
                            <i class="fas fa-check-circle"></i> تفعيل الأمر
                        </button>
                    </form>
                @endif
                
                @if ($productionOrder->status == 'released')
                    <form action="{{ route('production_orders.start', $productionOrder->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('هل أنت متأكد من بدء الإنتاج؟')">
                            <i class="fas fa-play"></i> بدء الإنتاج
                        </button>
                    </form>
                @endif
                
                @if ($productionOrder->status == 'in_progress')
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="fas fa-check"></i> إكمال الإنتاج
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal لإكمال الإنتاج -->
@if ($productionOrder->status == 'in_progress')
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إكمال الإنتاج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('production_orders.complete', $productionOrder->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="actual_quantity" class="form-label">الكمية الفعلية المنتجة</label>
                        <input type="number" step="0.01" class="form-control" id="actual_quantity" 
                               name="actual_quantity" value="{{ $productionOrder->quantity }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">المستودع</label>
                        <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                            <option value="">اختر المستودع</option>
                            @foreach(\App\Models\Warehouse::all() as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">إكمال الإنتاج</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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