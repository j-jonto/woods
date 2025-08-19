@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h1>قوائم المواد (BOM)</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('boms.create') }}" class="btn btn-primary">إضافة قائمة مواد جديدة</a>
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
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mt-4">
        <div class="card-body">
            @if($boms->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المنتج النهائي</th>
                                <th>المواد الخام</th>
                                <th>الكمية</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boms as $bom)
                                <tr>
                                    <td>{{ $bom->id }}</td>
                                    <td>
                                        <strong>{{ $bom->finishedGood->name ?? 'غير محدد' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $bom->finishedGood->code ?? 'غير محدد' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $bom->rawMaterial->name ?? 'غير محدد' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $bom->rawMaterial->code ?? 'غير محدد' }}</small>
                                    </td>
                                    <td>
                                        {{ $bom->quantity }} 
                                        {{ $bom->rawMaterial->unit_of_measure ?? 'وحدة' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $bom->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $bom->is_active ? 'نشطة' : 'غير نشطة' }}
                                        </span>
                                    </td>
                                    <td>{{ $bom->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('boms.show', $bom->id) }}" 
                                               class="btn btn-sm btn-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('boms.edit', $bom->id) }}" 
                                               class="btn btn-sm btn-warning" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('boms.destroy', $bom->id) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        title="حذف" 
                                                        onclick="return confirm('هل أنت متأكد من حذف قائمة المواد: {{ $bom->finishedGood->name ?? 'غير محدد' }}؟')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $boms->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد قوائم مواد</h5>
                    <p class="text-muted">ابدأ بإنشاء قائمة مواد جديدة</p>
                    <a href="{{ route('boms.create') }}" class="btn btn-primary">إضافة قائمة مواد</a>
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
    
    // إضافة تأثيرات بصرية للأزرار
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>
@endpush
@endsection 