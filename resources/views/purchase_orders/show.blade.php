@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تفاصيل أمر الشراء: {{ $purchaseOrder->order_no }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            معلومات الطلب
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>رقم الطلب:</strong> {{ $purchaseOrder->order_no }}</p>
                    <p><strong>المورد:</strong> {{ $purchaseOrder->supplier->name }}</p>
                    <p><strong>تاريخ الطلب:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('Y-m-d') }}</p>
                    <p><strong>تاريخ التسليم:</strong> {{ $purchaseOrder->delivery_date ? \Carbon\Carbon::parse($purchaseOrder->delivery_date)->format('Y-m-d') : 'غير محدد' }}</p>
                    <p><strong>طريقة الدفع:</strong> 
                        @if($purchaseOrder->payment_type == 'cash')
                            <span class="badge bg-success">نقدًا</span>
                        @else
                            <span class="badge bg-warning">آجل</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>المبلغ الإجمالي:</strong> ${{ number_format($purchaseOrder->total_amount, 2) }}</p>
                    <p><strong>الحالة:</strong> 
                        @if($purchaseOrder->status == 'draft')
                            <span class="badge bg-secondary">مسودة</span>
                        @elseif($purchaseOrder->status == 'ordered')
                            <span class="badge bg-warning">مطلوب</span>
                        @elseif($purchaseOrder->status == 'received')
                            <span class="badge bg-success">مستلم</span>
                        @else
                            <span class="badge bg-danger">ملغي</span>
                        @endif
                    </p>
                    <p><strong>ملاحظات:</strong> {{ $purchaseOrder->notes ?? 'لا توجد ملاحظات' }}</p>
                    <p><strong>تم الإنشاء بواسطة:</strong> {{ $purchaseOrder->creator->name ?? 'النظام' }}</p>
                    <p><strong>آخر تحديث:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->updated_at)->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            أصناف الطلب
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th class="text-end">الكمية</th>
                        <th class="text-end">سعر الوحدة</th>
                        <th class="text-end">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $item)
                        <tr>
                            <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }} {{ $item->item->unit_of_measure }}</td>
                            <td class="text-end">${{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end">${{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">المبلغ الإجمالي:</th>
                        <th class="text-end">${{ number_format($purchaseOrder->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">الإجراءات</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> العودة للقائمة
                </a>
                
                @if ($purchaseOrder->status == 'draft' || $purchaseOrder->status == 'ordered')
                    <a href="{{ route('purchase_orders.edit', $purchaseOrder->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> تعديل الطلب
                    </a>
                @endif
                
                @if ($purchaseOrder->status == 'draft')
                    <form action="{{ route('purchase_orders.confirm', $purchaseOrder->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('هل أنت متأكد من تأكيد أمر الشراء؟')">
                            <i class="fas fa-check"></i> تأكيد الطلب
                        </button>
                    </form>
                @endif
                
                @if ($purchaseOrder->status == 'ordered')
                    <form action="{{ route('purchase_orders.receive', $purchaseOrder->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-primary" 
                                onclick="return confirm('هل أنت متأكد من استلام البضاعة؟ سيتم تحديث المخزون تلقائياً.')">
                            <i class="fas fa-truck"></i> استلام البضاعة
                        </button>
                    </form>
                @endif
                
                @if ($purchaseOrder->status != 'received' && $purchaseOrder->status != 'cancelled')
                    <form action="{{ route('purchase_orders.destroy', $purchaseOrder->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" 
                                onclick="return confirm('هل أنت متأكد من حذف أمر الشراء؟')">
                            <i class="fas fa-trash"></i> حذف الطلب
                        </button>
                    </form>
                @endif
            </div>
            
            @if($purchaseOrder->status == 'draft')
                <div class="alert alert-info mt-3">
                    <small><i class="fas fa-info-circle"></i> أمر الشراء في حالة مسودة. يجب تأكيده أولاً قبل إرساله للمورد.</small>
                </div>
            @elseif($purchaseOrder->status == 'ordered')
                <div class="alert alert-warning mt-3">
                    <small><i class="fas fa-info-circle"></i> أمر الشراء مؤكد ومُرسل للمورد. عند استلام البضاعة سيتم تحديث المخزون تلقائياً.</small>
                </div>
            @elseif($purchaseOrder->status == 'received')
                <div class="alert alert-success mt-3">
                    <small><i class="fas fa-info-circle"></i> 
                        تم استلام البضاعة وتحديث المخزون بنجاح.
                        @if($purchaseOrder->payment_type == 'cash')
                            تم خصم المبلغ من الخزنة العامة.
                        @else
                            تم إنشاء التزام دفع آجل للمورد.
                        @endif
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 